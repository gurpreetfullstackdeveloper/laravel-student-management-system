<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'body', 'published_at', 'is_published', 'created_by'];
    protected $casts = ['published_at' => 'datetime', 'is_published' => 'boolean'];

    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}