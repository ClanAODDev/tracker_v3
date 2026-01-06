<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Division;
use App\Models\Member;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionPartTimeMemberRemoved extends Notification implements ShouldQueue
{
    use DivisionSettableNotification, Queueable, RetryableNotification;

    private Division $primaryDivision;

    private string $alertSetting = 'chat_alerts.pt_member_removed';

    /**
     * Create a new notification instance.
     *
     * @param  $partTimeDivision
     */
    public function __construct(private readonly Member $member, private readonly string $removalReason)
    {
        $this->primaryDivision = $this->member->division;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
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
        $handle = $this->member->handles->filter(fn ($handle) => $handle->id === $notifiable->handle_id)->first();

        return new BotChannelMessage($notifiable)
            ->title($this->primaryDivision->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($this->primaryDivision->getLogoPath())
            ->fields([
                [
                    'name' => 'Part Time Member Removed',
                    'value' => sprintf(
                        ':door: [%s](%s) [%d] was removed from %s and they were a part-time member in your division',
                        $this->member->name,
                        route('member', $this->member->getUrlParams()),
                        $this->member->clan_id,
                        $this->primaryDivision->name,
                    ),
                ],
                [
                    'name' => 'Reason',
                    'value' => addslashes($this->removalReason),
                ],
                [
                    'name' => $handle->label ?? 'In-Game Handle',
                    'value' => $handle->pivot->value ?? 'N/A',
                ],
            ])->error()
            ->send();
    }
}
