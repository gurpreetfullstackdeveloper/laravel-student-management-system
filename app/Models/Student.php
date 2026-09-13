<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'admission_no',
        'date_of_birth',
        'gender',
        'guardian_name',
        'guardian_phone',
        'address',
        'admission_date',
        'photo_path',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function attendances() { return $this->hasMany(Attendance::class); }
    public function results() { return $this->hasMany(ExamResult::class); }
    public function fees() { return $this->hasMany(StudentFee::class); }
}