<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberTransferred extends Notification
{
    use Queueable;

    private Member $member;

    /**
     * Create a new notification instance.
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
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
        $channel = $notifiable->settings()->get('slack_channel');

        return (new DiscordMessage())
            ->info()
            ->to($channel)
            ->fields([
                [
                    'name' => '**MEMBER TRANSFER**',
                    'value' => addslashes(":recycle: {$this->member->name} [{$this->member->clan_id}] transferred to {$notifiable->name}"),
                ],
            ])->send();
    }
}
