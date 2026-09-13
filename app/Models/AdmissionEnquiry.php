<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionEnquiry extends Model
{
    use HasFactory;

    protected $fillable = ['student_name', 'parent_name', 'phone', 'email', 'class_applied_for', 'message', 'status'];
}