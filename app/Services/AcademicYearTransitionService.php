<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AcademicYearTransitionService
 *
 * Handles carrying forward academic structure (classes, sections, homeroom teachers,
 * and subject teacher assignments) from one academic year to another.
 *
 * This is the teacher equivalent of the student bulk-enrollment process.
 * When a new academic year begins, admin can use this service to:
 *   1. Copy ClassRoom records from source AY → target AY
 *   2. Copy Section records (with homeroom teachers) under the new classes
 *   3. Copy TeacherAssignment records (subject-class-section mappings) to the new AY
 */
class AcademicYearTransitionService
{
    /**
     * Preview what will be carried forward without making any changes.
     *
     * Returns counts and details for the transition form.
     */
    public function preview(int $sourceAyId, int $targetAyId): array
    {
        $sourceAy = AcademicYear::findOrFail($sourceAyId);
        $targetAy = AcademicYear::findOrFail($targetAyId);

        $sourceClasses = ClassRoom::where('academic_year_id', $sourceAyId)->get();
        $targetClasses = ClassRoom::where('academic_year_id', $targetAyId)->get();
        $targetClassNames = $targetClasses->pluck('name')->toArray();

        $classesToCopy = $sourceClasses->filter(fn ($c) => ! in_array($c->name, $targetClassNames));

        // Sections under classes that will be copied
        $sectionsToCopy = Section::whereIn('class_id', $classesToCopy->pluck('id'))->get();

        // Teacher assignments for source AY
        $sourceAssignments = TeacherAssignment::where('academic_year_id', $sourceAyId)->get();

        // Already existing assignments in target AY
        $targetAssignments = TeacherAssignment::where('academic_year_id', $targetAyId)->count();

        return [
            'source_ay' => $sourceAy,
            'target_ay' => $targetAy,
            'source_classes_count' => $sourceClasses->count(),
            'target_classes_count' => $targetClasses->count(),
            'classes_to_copy_count' => $classesToCopy->count(),
            'already_exist_classes_count' => $sourceClasses->count() - $classesToCopy->count(),
            'sections_to_copy_count' => $sectionsToCopy->count(),
            'source_assignments_count' => $sourceAssignments->count(),
            'target_assignments_count' => $targetAssignments,
            'classes_to_copy' => $classesToCopy->values(),
            'sections_to_copy' => $sectionsToCopy->values(),
        ];
    }

    /**
     * Execute the academic year transition.
     *
     * @param int $sourceAyId Source academic year ID
     * @param int $targetAyId Target academic year ID
     * @param array $options Options: carry_classes, carry_sections, carry_assignments, clear_teacher_ids
     * @return array Results with counts
     */
    public function execute(int $sourceAyId, int $targetAyId, array $options = []): array
    {
        $carryClasses = $options['carry_classes'] ?? true;
        $carrySections = $options['carry_sections'] ?? true;
        $carryAssignments = $options['carry_assignments'] ?? true;
        $clearTeacherIds = $options['clear_teacher_ids'] ?? false;

        $results = [
            'classes_created' => 0,
            'classes_skipped' => 0,
            'sections_created' => 0,
            'assignments_created' => 0,
            'assignments_skipped' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            $sourceAy = AcademicYear::findOrFail($sourceAyId);
            $targetAy = AcademicYear::findOrFail($targetAyId);

            // ── Step 1: Copy Classes ──────────────────────────────
            $classIdMap = []; // old class ID => new class ID

            if ($carryClasses) {
                $sourceClasses = ClassRoom::where('academic_year_id', $sourceAyId)->get();
                $targetClassNames = ClassRoom::where('academic_year_id', $targetAyId)
                    ->pluck('name')
                    ->toArray();

                foreach ($sourceClasses as $sourceClass) {
                    if (in_array($sourceClass->name, $targetClassNames)) {
                        // Class already exists in target AY — map to the existing one
                        $existingTargetClass = ClassRoom::where('academic_year_id', $targetAyId)
                            ->where('name', $sourceClass->name)
                            ->first();
                        if ($existingTargetClass) {
                            $classIdMap[$sourceClass->id] = $existingTargetClass->id;
                            $results['classes_skipped']++;
                        }
                        continue;
                    }

                    $newClass = ClassRoom::create([
                        'branch_id' => $sourceClass->branch_id,
                        'academic_year_id' => $targetAyId,
                        'name' => $sourceClass->name,
                        'numeric_name' => $sourceClass->numeric_name,
                        'capacity' => $sourceClass->capacity,
                        'teacher_id' => $clearTeacherIds ? null : $sourceClass->teacher_id,
                    ]);

                    $classIdMap[$sourceClass->id] = $newClass->id;
                    $results['classes_created']++;
                }
            } else {
                // Even if not carrying classes, we need the mapping for sections/assignments
                // Map source classes to existing target classes by name
                $sourceClasses = ClassRoom::where('academic_year_id', $sourceAyId)->get();
                $targetClasses = ClassRoom::where('academic_year_id', $targetAyId)->get();

                foreach ($sourceClasses as $sourceClass) {
                    $matchingTarget = $targetClasses->first(fn ($tc) => $tc->name === $sourceClass->name);
                    if ($matchingTarget) {
                        $classIdMap[$sourceClass->id] = $matchingTarget->id;
                    }
                }
            }

            // ── Step 2: Copy Sections ─────────────────────────────
            $sectionIdMap = []; // old section ID => new section ID

            if ($carrySections && ! empty($classIdMap)) {
                $sourceSections = Section::whereIn('class_id', array_keys($classIdMap))->get();

                foreach ($sourceSections as $sourceSection) {
                    $newClassId = $classIdMap[$sourceSection->class_id] ?? null;
                    if (! $newClassId) {
                        continue;
                    }

                    // Check if section already exists in target class
                    $existingSection = Section::where('class_id', $newClassId)
                        ->where('name', $sourceSection->name)
                        ->first();

                    if ($existingSection) {
                        $sectionIdMap[$sourceSection->id] = $existingSection->id;
                        continue;
                    }

                    $newSection = Section::create([
                        'class_id' => $newClassId,
                        'name' => $sourceSection->name,
                        'max_students' => $sourceSection->max_students,
                        'teacher_id' => $clearTeacherIds ? null : $sourceSection->teacher_id,
                    ]);

                    $sectionIdMap[$sourceSection->id] = $newSection->id;
                    $results['sections_created']++;
                }
            } elseif (! empty($classIdMap)) {
                // Map existing sections by name under mapped classes
                foreach ($classIdMap as $oldClassId => $newClassId) {
                    $sourceSections = Section::where('class_id', $oldClassId)->get();
                    $targetSections = Section::where('class_id', $newClassId)->get();

                    foreach ($sourceSections as $sourceSection) {
                        $matchingTarget = $targetSections->first(fn ($ts) => $ts->name === $sourceSection->name);
                        if ($matchingTarget) {
                            $sectionIdMap[$sourceSection->id] = $matchingTarget->id;
                        }
                    }
                }
            }

            // ── Step 3: Copy Teacher Assignments ──────────────────
            if ($carryAssignments && ! empty($classIdMap)) {
                $sourceAssignments = TeacherAssignment::where('academic_year_id', $sourceAyId)->get();

                foreach ($sourceAssignments as $sourceAssignment) {
                    $newClassId = $classIdMap[$sourceAssignment->class_id] ?? null;
                    if (! $newClassId) {
                        $results['assignments_skipped']++;
                        continue;
                    }

                    // Map section ID if the assignment has one
                    $newSectionId = null;
                    if ($sourceAssignment->section_id) {
                        $newSectionId = $sectionIdMap[$sourceAssignment->section_id] ?? null;
                        if (! $newSectionId && $carrySections) {
                            // Section was not mapped — try to find by name
                            $sourceSection = Section::find($sourceAssignment->section_id);
                            if ($sourceSection) {
                                $existingTargetSection = Section::where('class_id', $newClassId)
                                    ->where('name', $sourceSection->name)
                                    ->first();
                                if ($existingTargetSection) {
                                    $newSectionId = $existingTargetSection->id;
                                    $sectionIdMap[$sourceAssignment->section_id] = $existingTargetSection->id;
                                }
                            }
                        }
                    }

                    // Check for duplicate
                    $exists = TeacherAssignment::where('academic_year_id', $targetAyId)
                        ->where('class_id', $newClassId)
                        ->where('subject_id', $sourceAssignment->subject_id)
                        ->where('section_id', $newSectionId)
                        ->where('teacher_id', $clearTeacherIds ? null : $sourceAssignment->teacher_id)
                        ->exists();

                    if ($exists) {
                        $results['assignments_skipped']++;
                        continue;
                    }

                    TeacherAssignment::create([
                        'teacher_id' => $clearTeacherIds ? null : $sourceAssignment->teacher_id,
                        'class_id' => $newClassId,
                        'section_id' => $newSectionId,
                        'subject_id' => $sourceAssignment->subject_id,
                        'academic_year_id' => $targetAyId,
                    ]);

                    $results['assignments_created']++;
                }
            }

            // ── Step 4: Recalculate capacities for new classes ────
            foreach ($classIdMap as $newClassId) {
                $class = ClassRoom::find($newClassId);
                if ($class) {
                    $class->recalculateCapacity();
                }
            }

            DB::commit();

            Log::info('Academic year transition completed', [
                'source_ay_id' => $sourceAyId,
                'target_ay_id' => $targetAyId,
                'options' => $options,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
            Log::error('Academic year transition failed', [
                'source_ay_id' => $sourceAyId,
                'target_ay_id' => $targetAyId,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }
}
