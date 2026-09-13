<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Notice::class);
        $notices = Notice::where('is_published', true)->whereHas('targets', fn ($query) => $query->where('audience_type', 'all')->orWhere(fn ($role) => $role->where('audience_type', 'role')->whereNull('audience_id')))->latest('published_at')->paginate(15);
        return view('teacher.notices.index', compact('notices'));
    }
}