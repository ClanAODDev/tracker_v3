<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionFailedPromotionAcceptance extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(private readonly Member $member) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => sprintf(
                        '%s was sent a promotion acceptance message, but their Discord privacy settings prevent receiving it. Please work with the user to update their settings. ',
                        $this->member->name
                    ),
                    'value' => sprintf(
                        'Discord - %s (%s)',
                        $this->member->discord,
                        $this->member->discord_id,
                    ),
                ],
            ])
            ->error()
            ->send();
    }
}
