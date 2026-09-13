<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TeacherAssignment;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Student::class);
        $teacher = auth()->user()->teacher;
        $assignments = $teacher->assignments()->get(['class_id', 'section_id', 'academic_year_id']);
        $students = Student::query()->with(['user', 'enrollments.schoolClass', 'enrollments.section', 'enrollments.academicYear'])
            ->whereHas('enrollments', function ($query) use ($assignments) {
                $query->where(function ($scoped) use ($assignments) {
                    foreach ($assignments as $assignment) {
                        $scoped->orWhere(fn ($match) => $match->where('class_id', $assignment->class_id)->where('section_id', $assignment->section_id)->where('academic_year_id', $assignment->academic_year_id));
                    }
                });
            })->paginate(20);
        return view('teacher.students.index', compact('students'));
    }
}