<?php

namespace App\Policies;

use App\Models\ExamSubject;
use App\Models\User;

class ExamSubjectPolicy
{
    public function viewAny(User $user): bool { return $user->role === 'super_admin'; }
    public function view(User $user, ExamSubject $subject): bool { return $user->role === 'super_admin'; }
    public function create(User $user): bool { return $user->role === 'super_admin'; }
    public function update(User $user, ExamSubject $subject): bool { return $user->role === 'super_admin'; }
    public function delete(User $user, ExamSubject $subject): bool { return $user->role === 'super_admin'; }
}