<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'subject_id', 'max_marks', 'pass_marks'];
    protected $casts = ['max_marks' => 'decimal:2', 'pass_marks' => 'decimal:2'];

    public function exam() { return $this->belongsTo(Exam::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function marks() { return $this->hasMany(Mark::class); }
}