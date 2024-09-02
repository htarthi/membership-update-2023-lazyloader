<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExportSubscribersCsvMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    /**
     * Create a new message instance.
     */
    public function __construct($url)
    {

        $this->url = $url;
    }

    // /**
    //  * Get the message envelope.
    //  */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Simplee Memberships export is ready',

    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'mail.exportscsv',
    //         with: ['url' => $this->url],

    //     );
    // }

    public function build()
    {
        return $this->subject('Simplee Memberships export is ready')
            ->from(env('NOTIFY_FROM_EMAIL'), 'Simplee Memberships')
            ->view('mail.exportscsv', ['url' => $this->url]);
    }
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
}
