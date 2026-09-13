<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\Notice;
use App\Models\StudentEnrollment;
use App\Models\StudentFee;
use App\Models\Mark;
use App\Models\TeacherAssignment;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\FeeStructure;
use App\Models\Event;
use App\Models\News;
use App\Models\Gallery;
use App\Models\AdmissionEnquiry;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Policies\AttendancePolicy;
use App\Policies\ExamResultPolicy;
use App\Policies\NoticePolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentEnrollmentPolicy;
use App\Policies\StudentFeePolicy;
use App\Policies\MarkPolicy;
use App\Policies\TeacherAssignmentPolicy;
use App\Policies\FeePaymentPolicy;
use App\Policies\FeeTypePolicy;
use App\Policies\FeeStructurePolicy;
use App\Policies\PublicContentPolicy;
use App\Policies\ExamPolicy;
use App\Policies\ExamSubjectPolicy;
use App\Policies\TeacherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Student::class => StudentPolicy::class,
        Teacher::class => TeacherPolicy::class,
        StudentEnrollment::class => StudentEnrollmentPolicy::class,
        Attendance::class => AttendancePolicy::class,
        ExamResult::class => ExamResultPolicy::class,
        StudentFee::class => StudentFeePolicy::class,
        Notice::class => NoticePolicy::class,
        TeacherAssignment::class => TeacherAssignmentPolicy::class,
        Mark::class => MarkPolicy::class,
        Exam::class => ExamPolicy::class,
        ExamSubject::class => ExamSubjectPolicy::class,
        FeePayment::class => FeePaymentPolicy::class,
        FeeType::class => FeeTypePolicy::class,
        FeeStructure::class => FeeStructurePolicy::class,
        Event::class => PublicContentPolicy::class,
        News::class => PublicContentPolicy::class,
        Gallery::class => PublicContentPolicy::class,
        AdmissionEnquiry::class => PublicContentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
