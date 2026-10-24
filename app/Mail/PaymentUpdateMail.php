<?php

namespace App\Mail;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?OrderPayment $payment = null,
        public ?PaymentStatus $previousStatus = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Fizetési értesítés - '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.payment-update',
            with: [
                'order' => $this->order,
                'payment' => $this->payment,
                'previousStatus' => $this->previousStatus,
            ],
        );
    }
}
