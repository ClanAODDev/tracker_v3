<?php

namespace App\Notifications;

use App\Platoon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

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
     * @param $member
     * @param $division
     */
    public function __construct($member, $division)
    {
        $this->division = $division;
        $this->member = $member;
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

        $message = " just recruited `{$this->member->name}` into the {$this->division->name} Division!";

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content(auth()->user()->name . $message)
            ->attachment(function ($attachment) {
                $attachment->title('View Member Profile')
                    ->content(
                        route('member', $this->member->getUrlParams())
                    );
            });
    }
}
