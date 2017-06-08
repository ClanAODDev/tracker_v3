<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExternalRecruitment extends Mailable
{
    use Queueable, SerializesModels;

    public $recruit;
    public $recruiter;

    public function __construct($recruit, $recruiter)
    {
        $this->recruit = $recruit;
        $this->recruiter = $recruiter->member;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.external-recruitment');
    }
}
