<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\FeeType;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Notifications\FeeReceiptNotification;
use App\Services\FeeAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_assignment_service_assigns_structure_items_with_discount_and_is_idempotent(): void
    {
        [$year, $schoolClass, $student] = $this->studentFixture();
        $structure = FeeStructure::create(['academic_year_id' => $year->id, 'class_id' => $schoolClass->id, 'name' => 'Annual fees']);
        $item = FeeStructureItem::create([
            'fee_structure_id' => $structure->id,
            'fee_type_id' => FeeType::create(['name' => 'Tuition'])->id,
            'amount' => 1000,
            'due_date' => '2026-06-01',
        ]);

        $service = app(FeeAssignmentService::class);
        $this->assertSame(1, $service->assignToClass($structure, $year, $schoolClass->id, 150, 'Scholarship'));
        $this->assertSame(1, $service->assignToClass($structure, $year, $schoolClass->id, 150, 'Scholarship'));

        $fee = $student->fees()->sole();
        $this->assertSame(850.0, (float) $fee->amount_payable);
        $this->assertSame(150.0, (float) $fee->discount_amount);
        $this->assertSame(1, StudentFee::count());
        $this->assertSame($item->id, $fee->fee_structure_item_id);
    }

    public function test_outstanding_balance_is_derived_from_payments(): void
    {
        [$year, $schoolClass, $student] = $this->studentFixture();
        $fee = $this->feeFor($student, $year, $schoolClass, 1000);
        $admin = User::factory()->superAdmin()->create();

        FeePayment::create([
            'student_fee_id' => $fee->id,
            'received_by' => $admin->id,
            'amount_paid' => 250,
            'paid_at' => now(),
            'payment_method' => 'cash',
            'receipt_no' => 'RCPT-001',
        ]);

        $fee->refresh();
        $this->assertSame(250.0, $fee->paid_amount);
        $this->assertSame(750.0, $fee->outstanding_amount);
        $this->assertArrayNotHasKey('outstanding_amount', $fee->getAttributes());
    }

    public function test_payment_dispatches_a_receipt_notification_to_the_student(): void
    {
        Notification::fake();
        [$year, $schoolClass, $student] = $this->studentFixture();
        $fee = $this->feeFor($student, $year, $schoolClass, 500);
        $admin = User::factory()->superAdmin()->create();

        $payment = FeePayment::create([
            'student_fee_id' => $fee->id,
            'received_by' => $admin->id,
            'amount_paid' => 500,
            'paid_at' => now(),
            'payment_method' => 'bank_transfer',
            'receipt_no' => 'RCPT-002',
        ]);

        Notification::assertSentTo($student->user, FeeReceiptNotification::class, function ($notification) use ($payment) {
            return $notification->payment->is($payment);
        });
    }

    public function test_student_can_view_only_their_own_receipt(): void
    {
        [$year, $schoolClass, $student] = $this->studentFixture();
        $fee = $this->feeFor($student, $year, $schoolClass, 500);
        $admin = User::factory()->superAdmin()->create();
        $payment = FeePayment::create([
            'student_fee_id' => $fee->id,
            'received_by' => $admin->id,
            'amount_paid' => 100,
            'paid_at' => now(),
            'payment_method' => 'cash',
            'receipt_no' => 'RCPT-003',
        ]);
        $other = User::factory()->create(['role' => 'student']);

        $this->actingAs($student->user, 'web')->get(route('student.fee-payments.receipt', $payment))->assertOk()->assertSee('RCPT-003');
        $this->actingAs($other, 'web')->get(route('student.fee-payments.receipt', $payment))->assertForbidden();
    }

    public function test_payment_cannot_exceed_the_derived_outstanding_balance(): void
    {
        [$year, $schoolClass, $student] = $this->studentFixture();
        $fee = $this->feeFor($student, $year, $schoolClass, 100);
        $admin = User::factory()->superAdmin()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        FeePayment::create([
            'student_fee_id' => $fee->id,
            'received_by' => $admin->id,
            'amount_paid' => 101,
            'paid_at' => now(),
            'payment_method' => 'cash',
        ]);
    }

    private function feeFor(Student $student, AcademicYear $year, SchoolClass $schoolClass, float $amount): StudentFee
    {
        $structure = FeeStructure::create(['academic_year_id' => $year->id, 'class_id' => $schoolClass->id, 'name' => 'Term fees']);
        $item = FeeStructureItem::create([
            'fee_structure_id' => $structure->id,
            'fee_type_id' => FeeType::create(['name' => 'Exam fee'])->id,
            'amount' => $amount,
            'due_date' => '2026-06-01',
        ]);

        return $student->fees()->create([
            'fee_structure_item_id' => $item->id,
            'academic_year_id' => $year->id,
            'amount_payable' => $amount,
            'discount_amount' => 0,
        ]);
    }

    private function studentFixture(): array
    {
        $year = AcademicYear::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $schoolClass->id]);
        $student = Student::factory()->create();
        StudentEnrollment::create([
            'student_id' => $student->id,
            'class_id' => $schoolClass->id,
            'section_id' => $section->id,
            'academic_year_id' => $year->id,
            'roll_no' => '1',
            'status' => 'active',
        ]);

        return [$year, $schoolClass, $student];
    }
}
