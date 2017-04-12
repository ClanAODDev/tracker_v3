<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class MemberRemoved extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $user;
    /**
     * @var
     */
    private $member;

    /**
     * Create a new notification instance.
     *
     * @param $member
     */
    public function __construct($member)
    {
        $this->member = $member;
        // $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * @return mixed
     */
    public function toSlack()
    {
        $to = ($this->division->settings()->get('slack_channel'))
            ?: '@' . auth()->user()->name;

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content($this->member->name . " was removed by " . auth()->user()->name);
    }
}
