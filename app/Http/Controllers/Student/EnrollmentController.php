<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentEnrollment;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', StudentEnrollment::class);
        $enrollments = auth()->user()->student->enrollments()->with(['schoolClass', 'section', 'academicYear'])->latest()->paginate(15);

        return view('student.enrollments.index', compact('enrollments'));
    }
}