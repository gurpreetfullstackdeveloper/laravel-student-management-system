<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceBulkService
{
    public function record(User $user, TeacherAssignment $assignment, string $date, array $statuses): void
    {
        DB::transaction(function () use ($user, $assignment, $date, $statuses): void {
            $studentIds = $assignment->section->studentEnrollments()
                ->where('class_id', $assignment->class_id)
                ->where('academic_year_id', $assignment->academic_year_id)
                ->pluck('student_id');

            $invalidStudentIds = collect(array_keys($statuses))->diff($studentIds);
            if ($invalidStudentIds->isNotEmpty()) {
                throw ValidationException::withMessages(['statuses' => 'Attendance contains a student outside this assignment.']);
            }

            foreach ($statuses as $studentId => $status) {
                $attendance = Attendance::firstOrNew([
                    'student_id' => $studentId,
                    'date' => $date,
                ]);
                $attendance->fill([
                    'class_id' => $assignment->class_id,
                    'section_id' => $assignment->section_id,
                    'academic_year_id' => $assignment->academic_year_id,
                    'recorded_by' => $user->id,
                    'status' => $status,
                ]);
                Gate::forUser($user)->authorize($attendance->exists ? 'update' : 'create', $attendance);
                $attendance->save();
            }
        });
    }
}