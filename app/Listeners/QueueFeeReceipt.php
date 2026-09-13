<?php

namespace App\Listeners;

use App\Events\FeePaymentRecorded;
use App\Jobs\GenerateFeeReceipt;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueFeeReceipt implements ShouldQueue
{
    public function handle(FeePaymentRecorded $event): void
    {
        GenerateFeeReceipt::dispatch($event->payment->id);
    }
}