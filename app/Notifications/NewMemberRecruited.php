<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
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
        return [WebhookChannel::class];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toWebhook()
    {
        $channel = str_slug($this->division->name) . '-officers';

        return (new DiscordMessage())
            ->success()
            ->to($channel)
            ->fields([
                [
                    'name' => "**NEW MEMBER RECRUITED**",
                    'value=' => auth()->user()->name . " just recruited `{$this->member->name}` into the {$this->division->name} Division!"
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams())
                ]
            ])
            ->send();
    }
}
