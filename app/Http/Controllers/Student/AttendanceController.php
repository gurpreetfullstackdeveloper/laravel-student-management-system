<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);
        $month = $request->string('month')->toString();
        $attendance = auth()->user()->student->attendances()->with(['schoolClass', 'section', 'academicYear'])->when(
            preg_match('/^\d{4}-\d{2}$/', $month),
            fn ($query) => $query->where('date', 'like', $month.'%'),
        )->latest('date')->paginate(31)->withQueryString();

        return view('student.attendance.index', compact('attendance', 'month'));
    }
}