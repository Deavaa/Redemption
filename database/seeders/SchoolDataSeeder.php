<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\GradeScale;
use App\Models\Role;

class SchoolDataSeeder extends Seeder
{
    /**
     * Seeds ONLY the essential school infrastructure.
     * NO sample/mock students, teachers, subjects, or fake data.
     *
     * What this seeder creates:
     *   - Branches (Lebu + Tuludimtu)
     *   - Academic Year 2025/2026 + 3 Terms (with exam dates)
     *   - Classes G1-12 + Sections (Tuludimtu A-B, Lebu A-D)
     *   - Grade Scales (system defaults)
     *   - Admin role assignment
     *
     * What this seeder does NOT create (use the UI to add):
     *   - Students (add via the admin UI)
     *   - Teachers, principal, registrar, finance users
     *   - Subjects
     *   - Teacher assignments / homeroom assignments
     *   - Calendar events
     */
    public function run(): void
    {
        $this->command->info('Seeding School of Redemption data...');

        // ======================================================================
        // 1. BRANCHES
        // ======================================================================
        $lebuBranch = Branch::updateOrCreate(
            ['name' => 'Lebu Campus'],
            [
                'address' => 'Lebu, Addis Ababa, Ethiopia',
                'phone' => '0112345678',
                'email' => 'lebu@schoolofredemption.edu',
                'is_headquarters' => true,
                'is_active' => true,
                'order' => 1,
            ]
        );

        $tuludimtuBranch = Branch::updateOrCreate(
            ['name' => 'Tuludimtu Campus'],
            [
                'address' => 'Tuludimtu, Addis Ababa, Ethiopia',
                'phone' => '0113456789',
                'email' => 'tuludimtu@schoolofredemption.edu',
                'is_headquarters' => false,
                'is_active' => true,
                'order' => 2,
            ]
        );

        $this->command->info('  Branches: Lebu + Tuludimtu');

        // ======================================================================
        // 2. ACADEMIC YEAR
        // ======================================================================
        $ay = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            ['start_date' => '2025-09-01', 'end_date' => '2026-07-15', 'is_current' => true]
        );
        $this->command->info('  Academic Year: 2025/2026');

        // ======================================================================
        // 3. TERMS (with examination dates)
        // ======================================================================
        Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 1'],
            [
                'start_date' => '2025-09-01', 'end_date' => '2025-12-20',
                'term_number' => 1, 'is_active' => true,
                'exam_start_date' => '2025-11-24', 'exam_end_date' => '2025-12-12',
            ]
        );
        Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 2'],
            [
                'start_date' => '2026-01-15', 'end_date' => '2026-04-10',
                'term_number' => 2, 'is_active' => false,
                'exam_start_date' => '2026-03-16', 'exam_end_date' => '2026-04-03',
            ]
        );
        Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 3'],
            [
                'start_date' => '2026-04-20', 'end_date' => '2026-07-15',
                'term_number' => 3, 'is_active' => false,
                'exam_start_date' => '2026-06-15', 'exam_end_date' => '2026-07-03',
            ]
        );
        $this->command->info('  Terms: 3 (with exam dates)');

        // ======================================================================
        // 4. ADMIN ROLE ASSIGNMENT
        // ======================================================================
        $adminUser = User::where('email', 'admin@school.com')->first();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminUser && $adminRole && !$adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }
        $this->command->info('  Admin role assigned');

        // ======================================================================
        // 5. CLASSES
        //    Tuludimtu: G1-8 (Sections A & B)
        //    Lebu: G9-12 (Sections A-D)
        // ======================================================================
        $tuludimtuClasses = [];
        for ($g = 1; $g <= 8; $g++) {
            $tuludimtuClasses[$g] = ClassRoom::updateOrCreate(
                ['name' => 'Grade ' . $g, 'branch_id' => $tuludimtuBranch->id, 'academic_year_id' => $ay->id],
                ['numeric_name' => $g, 'teacher_id' => null, 'capacity' => 50]
            );
        }

        $lebuClasses = [];
        for ($g = 9; $g <= 12; $g++) {
            $lebuClasses[$g] = ClassRoom::updateOrCreate(
                ['name' => 'Grade ' . $g, 'branch_id' => $lebuBranch->id, 'academic_year_id' => $ay->id],
                ['numeric_name' => $g, 'teacher_id' => null, 'capacity' => 200]
            );
        }
        $this->command->info('  Classes: 12 (Tuludimtu G1-8 + Lebu G9-12)');

        // ======================================================================
        // 6. SECTIONS
        //    Tuludimtu: Sections A & B per grade (16 sections)
        //    Lebu: Sections A-D per grade (16 sections)
        // ======================================================================
        $allSections = [];
        $tuludimtuSectionLetters = ['A', 'B'];

        foreach ($tuludimtuClasses as $gradeNum => $class) {
            foreach ($tuludimtuSectionLetters as $letter) {
                $key = $gradeNum . '_' . $letter;
                $allSections[$key] = Section::updateOrCreate(
                    ['class_id' => $class->id, 'name' => 'Section ' . $letter],
                    ['max_students' => 40, 'teacher_id' => null]
                );
            }
        }

        $lebuSectionLetters = ['A', 'B', 'C', 'D'];
        foreach ($lebuClasses as $gradeNum => $class) {
            foreach ($lebuSectionLetters as $letter) {
                $key = $gradeNum . '_' . $letter;
                $allSections[$key] = Section::updateOrCreate(
                    ['class_id' => $class->id, 'name' => 'Section ' . $letter],
                    ['max_students' => 50, 'teacher_id' => null]
                );
            }
        }
        $this->command->info('  Sections: 32 (Tuludimtu: 16xA-B, Lebu: 16xA-D)');

        // ======================================================================
        // 7. GRADE SCALES (system defaults)
        // ======================================================================
        GradeScale::seedDefaults();
        $this->command->info('  Grade Scales: seeded');

        // ======================================================================
        // DONE
        // ======================================================================
        $this->command->newLine();
        $this->command->info('School data seeded successfully!');
        $this->command->newLine();
        $this->command->table(['Account', 'Email', 'Password', 'Role'], [
            ['Admin', 'admin@school.com', '123456', 'admin'],
        ]);
        $this->command->newLine();
        $this->command->warn('NOTE: No students, teachers, or subjects were seeded.');
        $this->command->warn('Use the admin UI to add students, teachers, subjects, and other data.');
    }
}
