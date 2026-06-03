<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use \Illuminate\Mail\Mailables\Attachment;

class CRMNoQuoteReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $email_lang = ($this->data['lang']) ? $this->data['lang'] : 'en';
        $subject_line = __('CRM Reminder', [], $email_lang) . ': ' . __('Follow Up Required', [], $email_lang);
        return new Envelope(
            subject: $subject_line,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $email_lang = ($this->data['lang']) ? (($this->data['lang'] == 'en') ? '' : ($this->data['lang'].'-'))  : '';
        return new Content(
            markdown: 'emails.'.$email_lang.'crm-noquote-reminder',
            with: ['data' => $this->data],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        
    }
}
