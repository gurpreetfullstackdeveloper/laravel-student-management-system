<?php

namespace App\Policies;

use App\Models\TeacherAssignment;
use App\Models\User;

class TeacherAssignmentPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['teacher', 'super_admin'], true); }

    public function view(User $user, TeacherAssignment $assignment): bool
    {
        return $user->role === 'super_admin'
            || ($user->role === 'teacher' && $assignment->teacher->user_id === $user->id);
    }
}