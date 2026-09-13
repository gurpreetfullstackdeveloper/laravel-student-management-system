<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_dashboard_and_profile(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'admission_no' => 'ADM-PORTAL']);

        $response = $this->actingAs($studentUser, 'web')->get(route('student.dashboard'));
        $response->assertOk()->assertSee('ADM-PORTAL');

        $this->actingAs($studentUser, 'web')->get(route('student.profile.edit'))->assertOk();
    }

    public function test_student_can_update_only_their_allowed_profile_fields(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'admission_no' => 'ADM-UPDATE']);

        $this->actingAs($studentUser, 'web')->put(route('student.profile.update'), [
            'guardian_phone' => '555-0100',
            'address' => 'New address',
        ])->assertRedirect(route('student.profile.edit'));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'guardian_phone' => '555-0100',
            'address' => 'New address',
        ]);
    }

    public function test_student_cannot_view_another_students_result(): void
    {
        [$studentUser, $otherStudent] = $this->twoStudents();
        $exam = Exam::create([
            'academic_year_id' => AcademicYear::factory()->create()->id,
            'class_id' => SchoolClass::factory()->create()->id,
            'name' => 'Final Exam',
            'type' => 'final',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-15',
        ]);
        $result = ExamResult::create(['student_id' => $otherStudent->id, 'exam_id' => $exam->id, 'is_published' => true]);

        $this->actingAs($studentUser, 'web')
            ->get(route('student.results.show', $result))
            ->assertForbidden();
    }

    public function test_student_cannot_view_another_students_fee(): void
    {
        [$studentUser, $otherStudent] = $this->twoStudents();
        $year = AcademicYear::factory()->create();
        $structure = FeeStructure::create(['academic_year_id' => $year->id, 'name' => 'Tuition']);
        $item = FeeStructureItem::create([
            'fee_structure_id' => $structure->id,
            'fee_type_id' => FeeType::create(['name' => 'Tuition'])->id,
            'amount' => 1000,
            'due_date' => '2026-01-31',
        ]);
        $fee = StudentFee::create([
            'student_id' => $otherStudent->id,
            'fee_structure_item_id' => $item->id,
            'academic_year_id' => $year->id,
            'amount_payable' => 1000,
            'discount_amount' => 0,
        ]);

        $this->actingAs($studentUser, 'web')
            ->get(route('student.fees.show', $fee))
            ->assertForbidden();
    }

    public function test_teacher_and_guests_cannot_enter_student_portal(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher, 'web')->get(route('student.dashboard'))->assertForbidden();
        $this->app['auth']->guard('web')->logout();
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    private function twoStudents(): array
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $otherUser = User::factory()->create(['role' => 'student']);

        return [
            $studentUser,
            Student::create(['user_id' => $otherUser->id, 'admission_no' => 'ADM-OTHER']),
        ];
    }
}
