<?php

namespace App\Policies;

use App\Models\StudentEnrollment;
use App\Models\User;

class StudentEnrollmentPolicy
{
    public function viewAny(User $user): bool { return $user->role === 'student' || $user->role === 'super_admin'; }
    public function view(User $user, StudentEnrollment $enrollment): bool { return $user->role === 'super_admin' || ($user->role === 'student' && $enrollment->student->user_id === $user->id); }
}