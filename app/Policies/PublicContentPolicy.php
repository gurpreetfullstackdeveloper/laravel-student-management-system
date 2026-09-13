<?php

namespace App\Policies;

use App\Models\User;

class PublicContentPolicy
{
    public function viewAny(User $user): bool { return $user->role === 'super_admin'; }
    public function view(User $user, object $record): bool { return $user->role === 'super_admin'; }
    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, object $record): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, object $record): bool { return $user->role === 'super_admin'; }
}