<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    /**
     * Search parents by name or phone (AJAX endpoint).
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['parents' => []]);
        }

        $parents = ParentModel::where(function ($q) use ($query) {
            $q->where('father_name', 'LIKE', "%{$query}%")
                ->orWhere('mother_name', 'LIKE', "%{$query}%")
                ->orWhere('guardian_name', 'LIKE', "%{$query}%")
                ->orWhere('father_phone', 'LIKE', "%{$query}%")
                ->orWhere('mother_phone', 'LIKE', "%{$query}%")
                ->orWhere('guardian_phone', 'LIKE', "%{$query}%");
        })
            ->withCount('students')
            ->limit(20)
            ->get();

        return response()->json([
            'parents' => $parents->map(fn ($p) => [
                'id' => $p->id,
                'father_name' => $p->father_name,
                'mother_name' => $p->mother_name,
                'guardian_name' => $p->guardian_name,
                'father_phone' => $p->father_phone,
                'mother_phone' => $p->mother_phone,
                'guardian_phone' => $p->guardian_phone,
                'guardian_relation' => $p->guardian_relation,
                'students_count' => $p->students_count,
                'display_name' => $p->father_name ?: ($p->guardian_name ?: $p->mother_name),
                'display_phone' => $p->father_phone ?: ($p->guardian_phone ?: $p->mother_phone),
            ]),
        ]);
    }

    /**
     * Store a new parent via AJAX.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'mother_phone' => 'nullable|string|max:20',
            'father_occupation' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_relation' => 'nullable|string|max:50',
            'student_id' => 'nullable|exists:students,id',
            'relation' => 'nullable|in:father,mother,guardian',
        ]);

        // At least one name must be provided
        if (empty($validated['father_name']) && empty($validated['mother_name']) && empty($validated['guardian_name'])) {
            return response()->json([
                'success' => false,
                'message' => 'At least one parent/guardian name is required.',
            ], 422);
        }

        $parent = DB::transaction(function () use ($validated) {
            // Create user account for the parent (optional)
            $user = null;
            $displayName = $validated['father_name'] ?: ($validated['guardian_name'] ?: $validated['mother_name']);
            $displayPhone = $validated['father_phone'] ?: ($validated['guardian_phone'] ?: $validated['mother_phone']);

            if ($displayPhone) {
                $user = User::create([
                    'name' => $displayName,
                    'email' => 'parent_' . time() . '_' . rand(100, 999) . '@redemption.edu',
                    'password' => bcrypt('123456'),
                    'role' => 'parent',
                    'phone' => $displayPhone,
                    'is_active' => true,
                ]);
            }

            $parent = ParentModel::create([
                'user_id' => $user?->id,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'father_phone' => $validated['father_phone'] ?? null,
                'mother_phone' => $validated['mother_phone'] ?? null,
                'father_occupation' => $validated['father_occupation'] ?? null,
                'mother_occupation' => $validated['mother_occupation'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
                'guardian_relation' => $validated['guardian_relation'] ?? null,
            ]);

            // Link to student if student_id provided
            if (!empty($validated['student_id'])) {
                $parent->students()->attach($validated['student_id'], [
                    'relation' => $validated['relation'] ?? 'guardian',
                ]);
            }

            return $parent;
        });

        return response()->json([
            'success' => true,
            'message' => 'Parent/Guardian added successfully.',
            'parent' => [
                'id' => $parent->id,
                'father_name' => $parent->father_name,
                'mother_name' => $parent->mother_name,
                'guardian_name' => $parent->guardian_name,
                'father_phone' => $parent->father_phone,
                'mother_phone' => $parent->mother_phone,
                'guardian_phone' => $parent->guardian_phone,
                'guardian_relation' => $parent->guardian_relation,
                'display_name' => $parent->father_name ?: ($parent->guardian_name ?: $parent->mother_name),
                'display_phone' => $parent->father_phone ?: ($parent->guardian_phone ?: $parent->mother_phone),
            ],
        ], 201);
    }

    /**
     * Link an existing parent to a student.
     */
    public function linkToStudent(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:parents,id',
            'student_id' => 'required|exists:students,id',
            'relation' => 'nullable|in:father,mother,guardian',
        ]);

        $student = Student::find($validated['student_id']);
        $relation = $validated['relation'] ?? 'guardian';

        // Sync without detaching (prevent duplicate)
        $student->parents()->syncWithoutDetaching([
            $validated['parent_id'] => ['relation' => $relation],
        ]);

        // Also update the student's guardian_name and guardian_phone from the parent
        $parent = ParentModel::find($validated['parent_id']);
        if ($parent) {
            $guardianName = $parent->guardian_name ?: ($parent->father_name ?: $parent->mother_name);
            $guardianPhone = $parent->guardian_phone ?: ($parent->father_phone ?: $parent->mother_phone);
            if ($guardianName) {
                $student->update([
                    'guardian_name' => $guardianName,
                    'guardian_phone' => $guardianPhone ?? $student->guardian_phone,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Parent linked to student successfully.',
        ]);
    }
}
