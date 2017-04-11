<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewMemberRecruited extends Notification
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

    /**
     * @return mixed
     */
    public function toSlack()
    {
        return (new SlackMessage())
            ->success()
            ->content("{$this->user->name} just recruited {$this->member->name}! :thumbsup;");
    }
}
