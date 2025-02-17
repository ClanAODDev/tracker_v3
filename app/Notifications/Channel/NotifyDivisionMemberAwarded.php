<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Award;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class NotifyDivisionMemberAwarded extends Notification implements ShouldQueue
{
    use DivisionSettableNotification, Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_awarded';

    public function __construct(private readonly string $member, private readonly Award $award) {}

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
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail(asset(Storage::url($this->award->image)))->fields([
                [
                    'name' => sprintf('%s received an award!', $this->member),
                    'value' => sprintf(
                        '%s - %s',
                        $this->award->name,
                        $this->award->description,
                    ),
                ],
            ])
            ->info()
            ->send();
    }
}
