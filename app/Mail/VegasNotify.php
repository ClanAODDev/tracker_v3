<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VegasNotify extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = auth()->user();

        return $this->subject('Clan AOD - Confirming your interest...')
            ->markdown('emails.vegas-notify', compact('user'));
    }
}
