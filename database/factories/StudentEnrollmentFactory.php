<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_id' => SchoolClass::factory(),
            'section_id' => Section::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'roll_no' => (string) fake()->unique()->numberBetween(1, 999),
            'status' => 'active',
        ];
    }
}