<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\TeacherAssignment;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['student', 'teacher', 'super_admin'], true);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->role === 'super_admin'
            || ($user->role === 'student' && $attendance->student->user_id === $user->id)
            || ($user->role === 'teacher' && $this->teacherOwnsAttendance($user, $attendance));
    }

    public function create(User $user, ?Attendance $attendance = null): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role === 'teacher') {
            return $attendance ? $this->teacherOwnsAttendance($user, $attendance) : (bool) $user->teacher;
        }

        return false;
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $this->create($user, $attendance)
            && $attendance->date?->isToday()
            && (bool) $attendance->academicYear?->is_current;
    }

    private function teacherOwnsAttendance(User $user, Attendance $attendance): bool
    {
        return $user->teacher && TeacherAssignment::query()
            ->where('teacher_id', $user->teacher->id)
            ->where('class_id', $attendance->class_id)
            ->where('section_id', $attendance->section_id)
            ->where('academic_year_id', $attendance->academic_year_id)
            ->exists();
    }
}
