<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $reason,
        public string $registerUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Akun Guru Ditolak',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher-rejected',
        );
    }
}