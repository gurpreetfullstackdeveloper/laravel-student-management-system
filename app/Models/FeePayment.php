<?php

namespace App\Models;

use App\Events\FeePaymentRecorded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class FeePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['student_fee_id', 'received_by', 'amount_paid', 'paid_at', 'payment_method', 'reference_no', 'receipt_no'];
    protected $casts = ['amount_paid' => 'decimal:2', 'paid_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            $fee = $payment->studentFee()->firstOrFail();
            $paid = (float) $fee->payments()->sum('amount_paid');
            $amount = (float) $payment->amount_paid;

            if ($amount <= 0 || $amount > ((float) $fee->amount_payable - $paid)) {
                throw ValidationException::withMessages(['amount_paid' => 'Payment exceeds the fee outstanding balance.']);
            }

            $payment->receipt_no ??= 'RCPT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        });

        static::created(fn (self $payment) => FeePaymentRecorded::dispatch($payment));
    }

    public function studentFee() { return $this->belongsTo(StudentFee::class); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
}