<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token,
        public string $roleLabel
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue à IFAM — Activez votre compte',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activation',
            with: [
                'user' => $this->user,
                'token' => $this->token,
                'roleLabel' => $this->roleLabel,
                'activationUrl' => route('activation.show', $this->token),
            ],
        );
    }
}
