<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use Illuminate\View\View;

class FeePaymentController extends Controller
{
    public function receipt(FeePayment $feePayment): View
    {
        $this->authorize('view', $feePayment);
        $feePayment->load(['studentFee.student.user', 'studentFee.feeStructureItem.feeType', 'studentFee.academicYear', 'receivedBy']);

        return view('student.fees.receipt', compact('feePayment'));
    }
}