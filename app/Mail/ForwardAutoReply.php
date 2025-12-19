<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ForwardAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public $emailAddress;
    public $subject_line;
    public $body_html;
    public $body_text;

    public function __construct($emailAddress, $subject_line, $body_html, $body_text)
    {
        $this->emailAddress = $emailAddress;
        $this->subject_line = $subject_line;
        $this->body_html = $body_html;
        $this->body_text = $body_text;
    }

    public function build(): self
    {        
        $to_email = (strtolower(env('APP_URL')) === "http://localhost:8000" || strtolower(config('app.url')) === "http://localhost:8000") ? 'mail2oxygeninfotech@gmail.com' : 'info@intravat.com';
        //$to_email = 'mail2oxygeninfotech@gmail.com';
        
        $html_message = "From Address: " . $this->emailAddress . "<br>" . ($this->body_html ?: nl2br(e($this->body_text)));
        
        Log::info($html_message);

        return $this->subject($this->subject_line)
                ->from(config('mail.from.address'), config('mail.from.name'))                
                ->to($to_email)
                ->html($html_message);


        // return $this->subject($this->subject_line)
        //     ->from(config('mail.from.address'), config('mail.from.name'))
        //     ->to('mail2oxygeninfotech@gmail.com')   // ← forward destination
        //     ->view('emails.forward-auto-reply')
        //     ->with([
        //         'body_html' => $this->body_html,
        //         'body_text' => $this->body_text
        //     ]);
    }
}
