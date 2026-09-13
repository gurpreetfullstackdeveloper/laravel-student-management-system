<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;

class ResultCalculatorService
{
    public function calculate(Student $student, Exam $exam): ResultData
    {
        $subjects = $exam->examSubjects()->with(['marks' => fn ($query) => $query->where('student_id', $student->id)])->get();
        $maximum = (float) $subjects->sum('max_marks');
        $total = (float) $subjects->sum(fn ($subject) => $subject->marks->sum('obtained_marks'));
        $percentage = $maximum > 0 ? round(($total / $maximum) * 100, 2) : 0.0;
        $grade = match (true) {
            $percentage >= 80 => 'A+',
            $percentage >= 70 => 'A',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
        $allSubjectsMarked = $subjects->isNotEmpty() && $subjects->every(fn ($subject) => $subject->marks->isNotEmpty());
        $passed = $maximum > 0
            && $allSubjectsMarked
            && $subjects->every(fn ($subject) => $subject->marks->sum('obtained_marks') >= $subject->pass_marks);

        return new ResultData($total, $maximum, $percentage, $grade, $passed);
    }
}