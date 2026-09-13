<?php

namespace App\Policies;

use App\Models\Mark;
use App\Models\TeacherAssignment;
use App\Models\User;

class MarkPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['teacher', 'super_admin'], true);
    }

    public function view(User $user, Mark $mark): bool
    {
        return $user->role === 'super_admin' || $this->create($user, $mark);
    }

    public function create(User $user, ?Mark $mark = null): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role !== 'teacher' || ! $user->teacher) {
            return false;
        }

        if ($mark === null) {
            // Called by Filament with only the class string — just confirm the user is a teacher.
            return true;
        }

        $teacher = $user->teacher;
        $mark->loadMissing(['examSubject.exam', 'examSubject.subject', 'student.enrollments']);

        $enrollmentSections = $mark->student->enrollments
            ->where('class_id', $mark->examSubject->exam->class_id)
            ->where('academic_year_id', $mark->examSubject->exam->academic_year_id)
            ->pluck('section_id');

        return TeacherAssignment::query()->where('teacher_id', $teacher->id)
            ->where('subject_id', $mark->examSubject->subject_id)
            ->where('class_id', $mark->examSubject->exam->class_id)
            ->where('academic_year_id', $mark->examSubject->exam->academic_year_id)
            ->whereIn('section_id', $enrollmentSections)
            ->exists();
    }

    public function update(User $user, Mark $mark): bool
    {
        return $this->create($user, $mark);
    }

    public function delete(User $user, Mark $mark): bool
    {
        return $user->role === 'super_admin';
    }
}
