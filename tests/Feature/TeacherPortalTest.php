<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_only_their_assigned_students_and_assignments(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignedFixture();
        $otherStudentUser = User::factory()->create(['role' => 'student']);
        Student::create(['user_id' => $otherStudentUser->id, 'admission_no' => 'ADM-UNASSIGNED']);

        $this->actingAs($teacherUser, 'web')->get(route('teacher.dashboard'))->assertOk()->assertSee($assignment->subject->name);
        $this->actingAs($teacherUser, 'web')->get(route('teacher.assignments.index'))->assertOk()->assertSee($assignment->subject->name);
        $this->actingAs($teacherUser, 'web')->get(route('teacher.students.index'))->assertOk()->assertSee($student->admission_no)->assertDontSee('ADM-UNASSIGNED');
    }

    public function test_teacher_can_record_attendance_for_an_assigned_section(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignedFixture();
        $date = now()->toDateString();

        $this->actingAs($teacherUser, 'web')->put(route('teacher.attendance.update', $assignment), [
            'date' => $date,
            'statuses' => [$student->id => 'present'],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'recorded_by' => $teacherUser->id, 'status' => 'present']);
    }

    public function test_teacher_cannot_record_attendance_for_an_unassigned_section(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignedFixture();
        $otherClass = SchoolClass::factory()->create();
        $otherSection = Section::factory()->create(['class_id' => $otherClass->id]);
        $otherAssignment = TeacherAssignment::factory()->create([
            'teacher_id' => Teacher::factory()->create()->id,
            'class_id' => $otherClass->id,
            'section_id' => $otherSection->id,
            'academic_year_id' => $assignment->academic_year_id,
        ]);

        $this->actingAs($teacherUser, 'web')->get(route('teacher.attendance.edit', $otherAssignment))->assertForbidden();
        $this->assertDatabaseMissing('attendances', ['student_id' => $student->id]);
    }

    public function test_teacher_can_enter_marks_only_for_an_assigned_subject(): void
    {
        [$teacherUser, $assignment, $student] = $this->assignedFixture();
        $exam = Exam::create([
            'academic_year_id' => $assignment->academic_year_id,
            'class_id' => $assignment->class_id,
            'name' => 'Unit Test',
            'type' => 'unit_test',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-02',
        ]);
        ExamSubject::create(['exam_id' => $exam->id, 'subject_id' => $assignment->subject_id, 'max_marks' => 100, 'pass_marks' => 40]);

        $this->actingAs($teacherUser, 'web')->put(route('teacher.marks.update', $assignment), [
            'exam_id' => $exam->id,
            'marks' => [$student->id => 85],
            'remarks' => [$student->id => 'Good work'],
        ])->assertRedirect();

        $this->assertDatabaseHas('marks', ['student_id' => $student->id, 'obtained_marks' => 85, 'entered_by' => $assignment->teacher_id]);
    }

    public function test_teacher_profile_is_editable_with_limited_fields(): void
    {
        [$teacherUser, $assignment] = $this->assignedFixture();

        $this->actingAs($teacherUser, 'web')->put(route('teacher.profile.update'), [
            'phone' => '555-0123',
            'qualification' => 'M.Ed.',
        ])->assertRedirect(route('teacher.profile.edit'));

        $this->assertDatabaseHas('teachers', ['id' => $assignment->teacher_id, 'phone' => '555-0123', 'qualification' => 'M.Ed.']);
    }

    private function assignedFixture(): array
    {
        $teacherUser = User::factory()->teacher()->create();
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'employee_code' => 'EMP-'.fake()->unique()->numberBetween(100, 999)]);
        $year = AcademicYear::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $schoolClass->id]);
        $subject = Subject::factory()->create();
        $assignment = TeacherAssignment::create(['teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'class_id' => $schoolClass->id, 'section_id' => $section->id, 'academic_year_id' => $year->id]);
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'admission_no' => 'ADM-'.fake()->unique()->numberBetween(100, 999)]);
        StudentEnrollment::create(['student_id' => $student->id, 'class_id' => $schoolClass->id, 'section_id' => $section->id, 'academic_year_id' => $year->id, 'roll_no' => '1', 'status' => 'active']);

        return [$teacherUser, $assignment, $student];
    }
}
