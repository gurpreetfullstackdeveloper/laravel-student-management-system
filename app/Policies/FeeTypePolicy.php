<?php

namespace App\Policies;

use App\Models\FeeType;
use App\Models\User;

class FeeTypePolicy
{
    public function viewAny(User $user): bool { return $user->role === 'super_admin'; }
    public function view(User $user, FeeType $type): bool { return $user->role === 'super_admin'; }
    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, FeeType $type): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, FeeType $type): bool { return $user->role === 'super_admin'; }
}