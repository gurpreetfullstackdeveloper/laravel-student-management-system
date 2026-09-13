<?php

namespace App\Jobs;

use App\Models\FeePayment;
use App\Notifications\FeeReceiptNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateFeeReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $paymentId)
    {
    }

    public function handle(): void
    {
        $payment = FeePayment::with('studentFee.student.user')->findOrFail($this->paymentId);
        $payment->studentFee->student->user->notify(new FeeReceiptNotification($payment));
    }
}