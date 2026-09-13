<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\StudentEnrollment;
use App\Models\TeacherAssignment;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(TeacherAssignment $assignment): View
    {
        $this->authorize('view', $assignment);
        $studentIds = StudentEnrollment::where('class_id', $assignment->class_id)->where('section_id', $assignment->section_id)->where('academic_year_id', $assignment->academic_year_id)->pluck('student_id');
        $results = ExamResult::with(['student.user', 'exam'])->whereIn('student_id', $studentIds)->whereHas('exam', fn ($query) => $query->where('class_id', $assignment->class_id)->where('academic_year_id', $assignment->academic_year_id))->latest()->paginate(20);
        return view('teacher.results.index', compact('assignment', 'results'));
    }
}