<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['teacher', 'super_admin'], true);
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function view(User $user, Student $student): bool
    {
        return match ($user->role) {
            'super_admin' => true,
            'student' => $student->user_id === $user->id,
            'teacher' => $this->teacherCanView($user, $student),
            default => false,
        };
    }

    private function teacherCanView(User $user, Student $student): bool
    {
        if (! $user->teacher) {
            return false;
        }

        foreach ($student->enrollments as $enrollment) {
            if (TeacherAssignment::where('teacher_id', $user->teacher->id)
                ->where('class_id', $enrollment->class_id)
                ->where('section_id', $enrollment->section_id)
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->exists()) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, Student $student): bool
    {
        return $user->role === 'super_admin'
            || ($user->role === 'student' && $student->user_id === $user->id);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->role === 'super_admin';
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->role === 'super_admin';
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $user->role === 'super_admin';
    }
}