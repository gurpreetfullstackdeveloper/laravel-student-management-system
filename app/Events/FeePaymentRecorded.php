<?php

namespace App\Events;

use App\Models\FeePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeePaymentRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public FeePayment $payment)
    {
    }
}