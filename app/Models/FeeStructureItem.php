<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureItem extends Model
{
    use HasFactory;

    protected $fillable = ['fee_structure_id', 'fee_type_id', 'amount', 'due_date'];
    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date'];

    public function structure() { return $this->belongsTo(FeeStructure::class, 'fee_structure_id'); }
    public function feeType() { return $this->belongsTo(FeeType::class); }
    public function studentFees() { return $this->hasMany(StudentFee::class); }
}