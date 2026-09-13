<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function attendances() { return $this->hasMany(Attendance::class); }
    public function exams() { return $this->hasMany(Exam::class); }
    public function feeStructures() { return $this->hasMany(FeeStructure::class); }
    public function studentFees() { return $this->hasMany(StudentFee::class); }
}