<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', StudentFee::class);
        $fees = auth()->user()->student->fees()->with(['feeStructureItem.feeType', 'academicYear'])->latest()->paginate(15);

        return view('student.fees.index', compact('fees'));
    }

    public function show(StudentFee $studentFee): View
    {
        $this->authorize('view', $studentFee);
        $studentFee->load(['feeStructureItem.feeType', 'academicYear', 'payments' => fn ($query) => $query->latest('paid_at')]);

        return view('student.fees.show', compact('studentFee'));
    }
}