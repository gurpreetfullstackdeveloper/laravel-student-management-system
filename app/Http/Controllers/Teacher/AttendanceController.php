<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AttendanceBulkRequest;
use App\Models\StudentEnrollment;
use App\Models\TeacherAssignment;
use App\Services\AttendanceBulkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function edit(TeacherAssignment $assignment, Request $request): View
    {
        $this->authorize('view', $assignment);
        $students = $this->studentsFor($assignment);
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();
        $statuses = $students->mapWithKeys(fn ($student) => [$student->id => $student->attendances()->whereDate('date', $date)->value('status') ?? 'present']);
        return view('teacher.attendance.edit', compact('assignment', 'students', 'date', 'statuses'));
    }

    public function update(AttendanceBulkRequest $request, TeacherAssignment $assignment, AttendanceBulkService $service): RedirectResponse
    {
        $this->authorize('view', $assignment);
        $studentIds = $this->studentsFor($assignment)->pluck('id');
        abort_unless(collect(array_keys($request->validated('statuses')))->diff($studentIds)->isEmpty(), 403);
        $service->record($request->user(), $assignment, $request->validated('date'), $request->validated('statuses'));
        return to_route('teacher.attendance.edit', ['assignment' => $assignment, 'date' => $request->validated('date')])->with('status', 'Attendance saved.');
    }

    private function studentsFor(TeacherAssignment $assignment)
    {
        return StudentEnrollment::query()->with('student.user')->where('class_id', $assignment->class_id)->where('section_id', $assignment->section_id)->where('academic_year_id', $assignment->academic_year_id)->get()->pluck('student');
    }
}