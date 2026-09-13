<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = ['exam_subject_id', 'student_id', 'entered_by', 'obtained_marks', 'remarks'];
    protected $casts = ['obtained_marks' => 'decimal:2'];

    public function examSubject() { return $this->belongsTo(ExamSubject::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function teacher() { return $this->belongsTo(Teacher::class, 'entered_by'); }
}