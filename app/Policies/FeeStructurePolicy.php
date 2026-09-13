<?php

namespace App\Policies;

use App\Models\FeeStructure;
use App\Models\User;

class FeeStructurePolicy
{
    public function viewAny(User $user): bool { return $user->role === 'super_admin'; }
    public function view(User $user, FeeStructure $structure): bool { return $user->role === 'super_admin'; }
    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, FeeStructure $structure): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, FeeStructure $structure): bool { return $user->role === 'super_admin'; }
}