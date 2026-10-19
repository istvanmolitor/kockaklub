<?php

namespace App\Mail;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Új üzenet érkezett a kapcsolatfelvételi űrlapon',
            replyTo: [$this->message->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.messages.new-contact-message',
            with: [
                'message' => $this->message,
                'adminUrl' => MessageResource::getUrl('edit', ['record' => $this->message]),
            ],
        );
    }
}
