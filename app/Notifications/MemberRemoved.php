<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
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
    public function __construct($member)
    {
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
        return [WebhookChannel::class];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toWebhook()
    {
        $division = $this->member->division;

        $channel = $division->settings()->get('slack_channel');

        return (new DiscordMessage())
            ->info()
            ->to($channel)
            ->fields([
                [
                    'name' => "**MEMBER REMOVED**",
                    'value' => addslashes(":door: {$this->member->name} [{$this->member->clan_id}] was removed from {$division->name} by " . auth()->user()->name)
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams())
                ]
            ])->send();
    }
}
