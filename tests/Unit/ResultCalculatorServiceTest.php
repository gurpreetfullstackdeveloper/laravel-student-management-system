<?php

namespace Tests\Unit;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ResultCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_boundaries_and_pass_status_are_calculated_from_raw_marks(): void
    {
        [$student, $exam, $examSubject] = $this->examFixture();
        $teacher = \App\Models\Teacher::factory()->create();

        \App\Models\Mark::create([
            'exam_subject_id' => $examSubject->id,
            'student_id' => $student->id,
            'entered_by' => $teacher->id,
            'obtained_marks' => 80,
        ]);

        $result = app(ResultCalculatorService::class)->calculate($student, $exam);

        $this->assertSame(80.0, $result->total);
        $this->assertSame(100.0, $result->maximum);
        $this->assertSame(80.0, $result->percentage);
        $this->assertSame('A+', $result->grade);
        $this->assertTrue($result->passed);
    }

    public function test_missing_marks_fail_instead_of_passing_vacuously(): void
    {
        [$student, $exam] = $this->examFixture();

        $result = app(ResultCalculatorService::class)->calculate($student, $exam);

        $this->assertSame(0.0, $result->total);
        $this->assertSame(100.0, $result->maximum);
        $this->assertSame('F', $result->grade);
        $this->assertFalse($result->passed);
    }

    public function test_zero_maximum_marks_returns_zero_percentage_without_division_error(): void
    {
        [$student, $exam] = $this->examFixture(['max_marks' => 0, 'pass_marks' => 0]);

        $result = app(ResultCalculatorService::class)->calculate($student, $exam);

        $this->assertSame(0.0, $result->percentage);
        $this->assertFalse($result->passed);
    }

    private function examFixture(array $subjectOverrides = []): array
    {
        $student = Student::factory()->create();
        $year = AcademicYear::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $exam = Exam::create([
            'academic_year_id' => $year->id,
            'class_id' => $schoolClass->id,
            'name' => 'Final',
            'type' => 'final',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-10',
        ]);
        $examSubject = ExamSubject::create(array_merge([
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'max_marks' => 100,
            'pass_marks' => 40,
        ], $subjectOverrides));

        return [$student, $exam, $examSubject];
    }
}
