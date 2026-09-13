<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_enrollment_is_unique_per_student_and_academic_year(): void
    {
        $student = Student::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $schoolClass->id]);

        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $schoolClass->id,
            'section_id' => $section->id,
        ]);

        $this->expectException(QueryException::class);

        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $schoolClass->id,
            'section_id' => $section->id,
        ]);
    }

    public function test_teacher_assignment_scope_is_unique(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $schoolClass->id]);
        $academicYear = AcademicYear::factory()->create();
        $assignment = [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $schoolClass->id,
            'section_id' => $section->id,
            'academic_year_id' => $academicYear->id,
        ];

        TeacherAssignment::create($assignment);

        $this->expectException(QueryException::class);

        TeacherAssignment::create($assignment);
    }

    public function test_class_subject_relationship_is_scoped_by_academic_year(): void
    {
        $schoolClass = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $classSubject = ClassSubject::factory()->create([
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->assertTrue($schoolClass->subjects->contains($subject));
        $this->assertSame($academicYear->id, $classSubject->academicYear->id);
        $this->assertSame($schoolClass->id, $classSubject->schoolClass->id);
        $this->assertSame($subject->id, $classSubject->subject->id);
    }

    public function test_students_do_not_store_current_class_directly(): void
    {
        $this->assertFalse(Schema::hasColumn('students', 'class_id'));
        $this->assertTrue(Schema::hasColumn('student_enrollments', 'class_id'));
        $this->assertTrue(Schema::hasColumn('student_enrollments', 'academic_year_id'));
    }
}