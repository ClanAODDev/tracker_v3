<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyDivisionFailedAwardApprovalNotice extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly Member $requester,
        private readonly string $member,
        private readonly string $awardName
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [BotChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->fields([
                [
                    'name' => sprintf(
                        '%s requested an award for %s that was approved [%s], but their Discord privacy settings prevent receiving it. Please work with the user to update their settings.',
                        $this->requester->name,
                        $this->member,
                        $this->awardName,
                    ),
                    'value' => sprintf(
                        'Discord - %s (%s)',
                        $this->requester->discord,
                        $this->requester->discord_id,
                    ),
                ],
            ])
            ->error()
            ->send();
    }
}
