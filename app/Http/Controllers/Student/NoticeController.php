<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Notice::class);
        $student = auth()->user()->student;
        $classIds = $student->enrollments()->pluck('class_id');
        $notices = Notice::query()->where('is_published', true)->whereHas('targets', function ($query) use ($classIds) {
            $query->where('audience_type', 'all')
                ->orWhere(fn ($role) => $role->where('audience_type', 'role')->whereNull('audience_id'))
                ->orWhere(fn ($class) => $class->where('audience_type', 'class')->whereIn('audience_id', $classIds));
        })->latest('published_at')->paginate(15);

        return view('student.notices.index', compact('notices'));
    }

    public function show(Notice $notice): View
    {
        $this->authorize('view', $notice);

        return view('student.notices.show', compact('notice'));
    }
}