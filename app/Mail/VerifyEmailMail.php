<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Usuario;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Usuario $usuario) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@condominio.com'), env('MAIL_FROM_NAME', 'Condominio')),
            subject: 'Verificar tu correo electrónico - Condominio',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $verificationUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/verify-email?token=' . $this->usuario->email_verification_token;

        return new Content(
            view: 'emails.verify-email',
            with: [
                'usuario' => $this->usuario,
                'verificationUrl' => $verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
