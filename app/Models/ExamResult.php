<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'exam_id', 'is_published', 'published_at', 'overall_remarks'];
    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(function (self $result): void {
            if ($result->is_published && ! $result->published_at) {
                $result->published_at = now();
            }

            if (! $result->is_published) {
                $result->published_at = null;
            }
        });
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function exam() { return $this->belongsTo(Exam::class); }
}