<?php

namespace App\Policies;

use App\Models\Notice;
use App\Models\User;

class NoticePolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['student', 'teacher', 'super_admin'], true); }

    public function view(User $user, Notice $notice): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        $classIds = $user->student?->enrollments()->pluck('class_id') ?? collect();

        return in_array($user->role, ['student', 'teacher'], true)
            && $notice->is_published
            && $notice->targets()->where(function ($query) use ($classIds) {
                $query->where('audience_type', 'all')
                    ->orWhere(function ($role) {
                        $role->where('audience_type', 'role')->whereNull('audience_id');
                    })
                    ->orWhere(function ($class) use ($classIds) {
                        $class->where('audience_type', 'class')->whereIn('audience_id', $classIds);
                    });
            })->exists();
    }

    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, Notice $notice): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, Notice $notice): bool { return $user->role === 'super_admin'; }
}