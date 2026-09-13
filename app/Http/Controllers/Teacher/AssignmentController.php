<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TeacherAssignment::class);
        $assignments = auth()->user()->teacher->assignments()->with(['schoolClass', 'section', 'subject', 'academicYear'])->latest()->paginate(15);
        return view('teacher.assignments.index', compact('assignments'));
    }
}