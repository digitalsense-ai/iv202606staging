<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use \Illuminate\Mail\Mailables\Attachment;

class LockGB extends Mailable
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
        $subject_line = __('Reported today - Payable amount to be registered on authorities Account:', [], $email_lang);      
        return new Envelope(
            subject: $subject_line .' - ' . $this->data['payment_date'],
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
            markdown: 'emails.'.$email_lang.'lockgb',
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
        $attachments = [];
              
        foreach ($this->data['attachment'] as $attachment) {        
            if($attachment['url'] != '')     
            {    
                $attachments[] = Attachment::fromData(fn () => $attachment['url']['file'], $attachment['text'].'.'.$attachment['url']['file_extension'])
                         ->withMime($attachment['url']['mime_type']);
            }
        }

        return $attachments;
    }
}
