<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberRemoved extends Notification
{
    use Queueable;

    private $user;

    private $member;

    /**
     * Create a new notification instance.
     */
    public function __construct($member)
    {
        $this->member = $member;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return [WebhookChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws \Exception
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('officer_channel');

        return (new DiscordMessage())
            ->info()
            ->to($channel)
            ->fields([
                [
                    'name' => '**MEMBER REMOVED**',
                    'value' => addslashes(":door: {$this->member->name} [{$this->member->clan_id}] was removed from {$notifiable->name} by " . auth()->user()->name),
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])->send();
    }
}
