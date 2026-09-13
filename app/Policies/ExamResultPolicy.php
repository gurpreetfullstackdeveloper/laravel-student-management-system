<?php

namespace App\Policies;

use App\Models\ExamResult;
use App\Models\TeacherAssignment;
use App\Models\User;

class ExamResultPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['student', 'teacher', 'super_admin'], true); }

    public function create(User $user): bool { return $user->role === 'super_admin'; }

    public function view(User $user, ExamResult $result): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role === 'student') {
            return $result->is_published && $result->student->user_id === $user->id;
        }

        if ($user->role !== 'teacher' || ! $user->teacher) {
            return false;
        }

        return $result->student->enrollments()
            ->whereHas('academicYear', fn ($query) => $query->whereKey($result->exam->academic_year_id))
            ->where('class_id', $result->exam->class_id)
            ->whereHas('student', fn ($query) => $query->whereKey($result->student_id))
            ->whereHas('section', function ($query) use ($user, $result) {
                $query->whereIn('id', TeacherAssignment::where('teacher_id', $user->teacher->id)
                    ->where('class_id', $result->exam->class_id)
                    ->where('academic_year_id', $result->exam->academic_year_id)
                    ->pluck('section_id'));
            })
            ->exists();
    }

    public function update(User $user, ExamResult $result): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, ExamResult $result): bool { return $user->role === 'super_admin'; }
}