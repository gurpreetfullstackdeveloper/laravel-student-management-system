<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeTarget extends Model
{
    protected $fillable = ['notice_id', 'audience_type', 'audience_id'];
    public $timestamps = false;

    public function notice() { return $this->belongsTo(Notice::class); }
}