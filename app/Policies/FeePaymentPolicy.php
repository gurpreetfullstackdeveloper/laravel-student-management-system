<?php

namespace App\Policies;

use App\Models\FeePayment;
use App\Models\User;

class FeePaymentPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['student', 'super_admin'], true); }

    public function view(User $user, FeePayment $payment): bool
    {
        return $user->role === 'super_admin'
            || ($user->role === 'student' && $payment->studentFee->student->user_id === $user->id);
    }

    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, FeePayment $payment): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, FeePayment $payment): bool { return $user->role === 'super_admin'; }
}