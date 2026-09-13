<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Notice;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 404);
        $year = AcademicYear::where('is_current', true)->first();
        $assignments = $teacher->assignments()->with(['schoolClass', 'section', 'subject'])->when($year, fn ($query) => $query->where('academic_year_id', $year->id))->get();
        $classIds = $assignments->pluck('class_id')->unique();
        $notices = Notice::where('is_published', true)->whereHas('targets', fn ($query) => $query->where('audience_type', 'all')->orWhere(fn ($role) => $role->where('audience_type', 'role')->whereNull('audience_id')))->latest('published_at')->limit(5)->get();

        return view('teacher.dashboard', compact('teacher', 'assignments', 'classIds', 'notices'));
    }
}