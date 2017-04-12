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
     * @param $reason
     */
    public function __construct($member, $reason)
    {
        $this->member = $member;
         $this->reason = $reason;
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
        $division = $this->member->primaryDivision;
        $to = ($division->settings()->get('slack_channel'))
            ?: '@' . auth()->user()->name;

        $reason = ($this->reason) ?: "None provided";

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content("{$this->member->name} [{$this->member->clan_id}] was removed by " . auth()->user()->name)
            ->attachment(function ($attachment) use ($reason) {
                $attachment->title('Reason')
                    ->content($reason);
            });
    }
}
