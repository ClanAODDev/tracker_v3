<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MemberProfileEdited extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $member
     */
    public function __construct($user, $member)
    {
        $this->user = $user;
        $this->member = $member;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack()
    {
        return (new SlackMessage())
            ->success()
            ->content("{$this->member->name} was updated by {$this->user->name}");
    }
}
