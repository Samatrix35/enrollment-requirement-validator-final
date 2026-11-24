<?php

namespace Database\Seeders;

use App\Models\Requirement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $studentPassword = (string) env('DEMO_STUDENT_PASSWORD', '');
        $staffPassword = (string) env('DEMO_STAFF_PASSWORD', '');

        if (strlen($studentPassword) < 12 || strlen($staffPassword) < 12) {
            throw new RuntimeException(
                'Set DEMO_STUDENT_PASSWORD and DEMO_STAFF_PASSWORD to unique values of at least 12 characters before seeding.',
            );
        }

        if (hash_equals($studentPassword, $staffPassword)) {
            throw new RuntimeException('Use different passwords for the demo student and staff accounts.');
        }

        $student = Student::updateOrCreate(
            ['student_number' => 'DEMO-2026-001'],
            [
                'first_name' => 'Jordan',
                'last_name' => 'Example',
                'email' => null,
                'course' => 'BSIT',
                'year_level' => '3',
                'section' => 'A',
            ],
        );

        User::updateOrCreate(
            ['email' => 'student.demo@example.test'],
            [
                'name' => 'Jordan Example',
                'password' => $studentPassword,
                'role' => 'student',
                'student_id' => $student->id,
            ],
        );

        User::updateOrCreate(
            ['email' => 'staff.demo@example.test'],
            [
                'name' => 'Enrollment Staff Demo',
                'password' => $staffPassword,
                'role' => 'enrollment_staff',
                'student_id' => null,
            ],
        );

        foreach ([
            'Birth Certificate',
            'Report Card / Transcript',
            'Certificate of Good Moral Character',
        ] as $requirementName) {
            Requirement::firstOrCreate(
                ['student_id' => $student->id, 'requirement_name' => $requirementName],
                ['status' => 'Pending'],
            );
        }
    }
}
