<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return match ($user->role) {
            'super_admin' => true,
            'teacher' => $teacher->user_id === $user->id,
            default => false,
        };
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->role === 'super_admin'
            || ($user->role === 'teacher' && $teacher->user_id === $user->id);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->role === 'super_admin';
    }

    public function restore(User $user, Teacher $teacher): bool
    {
        return $user->role === 'super_admin';
    }

    public function forceDelete(User $user, Teacher $teacher): bool
    {
        return $user->role === 'super_admin';
    }
}