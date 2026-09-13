<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Services\ResultCalculatorService;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ExamResult::class);
        $results = auth()->user()->student->results()->with(['exam.academicYear', 'exam.schoolClass'])->where('is_published', true)->latest()->paginate(15);

        return view('student.results.index', compact('results'));
    }

    public function show(ExamResult $examResult, ResultCalculatorService $calculator): View
    {
        $this->authorize('view', $examResult);
        $examResult->load(['exam.examSubjects.subject', 'student']);
        $result = $calculator->calculate($examResult->student, $examResult->exam);

        return view('student.results.show', compact('examResult', 'result'));
    }
}