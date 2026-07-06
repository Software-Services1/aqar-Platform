<?php

namespace App\Mail;

use App\Models\AdLicense;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiringLicenseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AdLicense $license) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تنبيه: ترخيص يقترب من الانتهاء');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.expiring-license');
    }
}
