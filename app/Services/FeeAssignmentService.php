<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeeAssignmentService
{
    public function assignToClass(FeeStructure $structure, AcademicYear $academicYear, int $classId, ?float $discountAmount = null, ?string $discountReason = null): int
    {
        if ($structure->academic_year_id !== $academicYear->id || ($structure->class_id !== null && $structure->class_id !== $classId)) {
            throw new InvalidArgumentException('The fee structure does not apply to this academic year or class.');
        }

        $items = $structure->items()->get();
        $students = Student::whereHas('enrollments', fn ($query) => $query
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active'))->get();

        return DB::transaction(function () use ($items, $students, $academicYear, $discountAmount, $discountReason): int {
            $assigned = 0;
            foreach ($students as $student) {
                foreach ($items as $item) {
                    $discount = min((float) ($discountAmount ?? 0), (float) $item->amount);
                    StudentFee::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'fee_structure_item_id' => $item->id,
                            'academic_year_id' => $academicYear->id,
                        ],
                        [
                            'amount_payable' => (float) $item->amount - $discount,
                            'discount_amount' => $discount,
                            'discount_reason' => $discountReason,
                        ],
                    );
                    $assigned++;
                }
            }

            return $assigned;
        });
    }
}