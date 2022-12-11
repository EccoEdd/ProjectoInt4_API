<?php

namespace App\Mail;

use App\Models\Incubator;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailNotificateVisitor extends Mailable
{
    use Queueable, SerializesModels;
    protected User $user;
    protected Incubator $incubator;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Incubator $incubator)
    {
        $this->user = $user;
        $this->incubator = $incubator;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Mail Notificate Visitor',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'notifyVisitorNew',
            with: [
                'name' => $this->user->name,
                'incu'  =>  $this->incubator->code,
                'inname' => $this->incubator->name
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
