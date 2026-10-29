<?php

declare(strict_types=1);

namespace App\Mails\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use stdClass;

class LinkResetPassword extends Mailable
{
    use Queueable;
    use SerializesModels;

    private stdClass $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(stdClass $data)
    {
        $this->data = clone $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                '%s - %s',
                trans('auth.email_title_reset_password'),
                config('app.name')
            ),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'back.emails.link-reset-password',
            with: ['data' => $this->data],
        );
    }
}
