<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404);
        $currentYear = AcademicYear::where('is_current', true)->first();

        return view('student.dashboard', [
            'student' => $student,
            'enrollment' => $student->enrollments()->with(['schoolClass', 'section', 'academicYear'])->when($currentYear, fn ($query) => $query->where('academic_year_id', $currentYear->id))->first(),
            'attendanceCount' => $student->attendances()->count(),
            'publishedResults' => $student->results()->where('is_published', true)->count(),
            'outstandingFees' => $student->fees()->get()->sum('outstanding_amount'),
        ]);
    }
}