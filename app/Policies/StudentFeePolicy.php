<?php

namespace App\Policies;

use App\Models\StudentFee;
use App\Models\User;

class StudentFeePolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['student', 'super_admin'], true); }

    public function view(User $user, StudentFee $fee): bool
    {
        return $user->role === 'super_admin'
            || ($user->role === 'student' && $fee->student->user_id === $user->id);
    }

    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, StudentFee $fee): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, StudentFee $fee): bool { return $user->role === 'super_admin'; }
}