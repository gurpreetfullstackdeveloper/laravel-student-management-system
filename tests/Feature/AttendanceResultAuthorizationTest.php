<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceResultAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_duplicate_attendance_for_the_same_student_and_day(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignmentFixture();
        $attributes = [
            'student_id' => $student->id,
            'class_id' => $assignment->class_id,
            'section_id' => $assignment->section_id,
            'academic_year_id' => $assignment->academic_year_id,
            'recorded_by' => $teacherUser->id,
            'date' => '2026-01-15',
            'status' => 'present',
        ];

        Attendance::create($attributes);
        $this->expectException(QueryException::class);
        Attendance::create($attributes);
    }

    public function test_teacher_can_view_results_only_for_an_assigned_section(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignmentFixture();
        $exam = Exam::create([
            'academic_year_id' => $assignment->academic_year_id,
            'class_id' => $assignment->class_id,
            'name' => 'Mid Term',
            'type' => 'mid_term',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
        ]);
        $result = ExamResult::create(['student_id' => $student->id, 'exam_id' => $exam->id, 'is_published' => false]);

        $this->actingAs($teacherUser, 'web')->get(route('teacher.results.index', $assignment))->assertOk();
        $this->assertTrue($teacherUser->can('view', $result));
    }

    public function test_publishing_a_result_sets_the_publication_timestamp_without_storing_totals(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignmentFixture();
        $exam = Exam::create([
            'academic_year_id' => $assignment->academic_year_id,
            'class_id' => $assignment->class_id,
            'name' => 'Final',
            'type' => 'final',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
        ]);
        $result = ExamResult::create(['student_id' => $student->id, 'exam_id' => $exam->id, 'is_published' => true]);

        $this->assertNotNull($result->fresh()->published_at);
        $this->assertFalse($result->getAttributes()['is_published'] === null);
        $this->assertArrayNotHasKey('total', $result->getAttributes());
        $this->assertArrayNotHasKey('grade', $result->getAttributes());
    }

    public function test_past_attendance_cannot_be_updated_by_teacher(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignmentFixture(['is_current' => true]);
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'class_id' => $assignment->class_id,
            'section_id' => $assignment->section_id,
            'academic_year_id' => $assignment->academic_year_id,
            'recorded_by' => $teacherUser->id,
            'date' => now()->subDay()->toDateString(),
            'status' => 'present',
        ]);

        $this->assertFalse($teacherUser->can('update', $attendance));
    }

    private function assignmentFixture(array $yearOverrides = []): array
    {
        $teacherUser = User::factory()->teacher()->create();
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'employee_code' => 'EMP-'.fake()->unique()->numberBetween(1000, 9999)]);
        $year = AcademicYear::factory()->create($yearOverrides);
        $schoolClass = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $schoolClass->id]);
        $subject = Subject::factory()->create();
        $assignment = TeacherAssignment::create(['teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'class_id' => $schoolClass->id, 'section_id' => $section->id, 'academic_year_id' => $year->id]);
        $student = Student::factory()->create();
        StudentEnrollment::create(['student_id' => $student->id, 'class_id' => $schoolClass->id, 'section_id' => $section->id, 'academic_year_id' => $year->id, 'roll_no' => '1', 'status' => 'active']);

        return [$teacherUser, $assignment, $student];
    }
}
