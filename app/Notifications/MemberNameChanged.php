<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MemberNameChanged extends Notification
{
    use Queueable;

    private $names;

    private $division;

    /**
     * Create a new notification instance.
     *
     * @param $names
     * @param $division
     */
    public function __construct($names, $division)
    {
        $this->names = $names;
        $this->division = $division;
    }

    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack()
    {
        $to = ($this->division->settings()->get('slack_channel'))
            ?: '@' . auth()->user()->name;

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content("*MEMBER STATUS - NAME CHANGE*\n`{$this->names['oldName']}` is now known as `{$this->names['newName']}`. Please inform the member of this change.");
    }
}
