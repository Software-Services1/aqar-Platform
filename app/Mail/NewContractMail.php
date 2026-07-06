<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContractMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'عقد جديد تم إسناده إليك');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.new-contract');
    }
}
