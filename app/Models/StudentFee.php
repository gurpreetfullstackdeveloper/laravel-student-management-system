<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'fee_structure_item_id', 'academic_year_id', 'amount_payable', 'discount_amount', 'discount_reason'];
    protected $casts = ['amount_payable' => 'decimal:2', 'discount_amount' => 'decimal:2'];

    public function student() { return $this->belongsTo(Student::class); }
    public function feeStructureItem() { return $this->belongsTo(FeeStructureItem::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function payments() { return $this->hasMany(FeePayment::class); }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return (float) $this->amount_payable - $this->paid_amount;
    }
}