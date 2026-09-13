<?php

namespace App\Notifications;

use App\Models\FeePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FeePayment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'fee_receipt',
            'receipt_no' => $this->payment->receipt_no,
            'amount_paid' => (float) $this->payment->amount_paid,
            'paid_at' => $this->payment->paid_at?->toIso8601String(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fee payment receipt '.$this->payment->receipt_no)
            ->greeting('Fee payment received')
            ->line('Receipt: '.$this->payment->receipt_no)
            ->line('Amount paid: '.number_format((float) $this->payment->amount_paid, 2))
            ->line('Payment method: '.str_replace('_', ' ', $this->payment->payment_method));
    }
}