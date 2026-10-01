<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Válasz az üzenetedre',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.messages.reply',
            with: ['message' => $this->message],
        );
    }
}
