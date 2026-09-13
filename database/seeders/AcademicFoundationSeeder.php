<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_current' => true,
            ],
        );

        $schoolClass = SchoolClass::firstOrCreate(
            ['name' => 'Class 10'],
            ['order' => 10],
        );

        $section = Section::firstOrCreate([
            'class_id' => $schoolClass->id,
            'name' => 'A',
        ]);

        $subjects = collect([
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Science', 'code' => 'SCI'],
        ])->map(fn (array $subject) => Subject::firstOrCreate(
            ['code' => $subject['code']],
            ['name' => $subject['name']],
        ));

        $subjects->each(fn (Subject $subject) => ClassSubject::firstOrCreate([
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
        ]));

        $studentUser = User::firstOrCreate(
            ['username' => 'student-001'],
            [
                'name' => 'Demo Student',
                'email' => null,
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_active' => true,
            ],
        );

        $student = Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            ['admission_no' => 'ADM-001'],
        );

        StudentEnrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'roll_no' => '1',
                'status' => 'active',
            ],
        );

        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@example.test'],
            [
                'name' => 'Demo Teacher',
                'username' => 'teacher-001',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'is_active' => true,
            ],
        );

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['employee_code' => 'EMP-001'],
        );

        TeacherAssignment::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects->first()->id,
            'class_id' => $schoolClass->id,
            'section_id' => $section->id,
            'academic_year_id' => $academicYear->id,
        ]);
    }
}