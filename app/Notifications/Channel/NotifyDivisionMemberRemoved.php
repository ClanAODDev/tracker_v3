<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Division;
use App\Models\Member;
use App\Models\Squad;
use App\Models\User;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionMemberRemoved extends Notification implements ShouldQueue
{
    use DivisionSettableNotification, Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_removed';

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly Member $member,
        private readonly ?User $remover,
        private readonly ?string $removalReason,
        private readonly ?Squad $squad = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return [BotChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toBot(Division $notifiable)
    {
        $removerName = $this->remover?->name ?? 'Forum sync';

        $handle = $this->member->handles->filter(fn ($handle) => $handle->id === $notifiable->handle_id)->first();
        $reason = $this->removalReason
            ? ['name' => 'Reason', 'value' => $this->removalReason]
            : null;

        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())->fields(array_values(array_filter([
                [
                    'name' => 'Member Removed',
                    'value' => sprintf(
                        ':door: [%s](%s) [%d] was removed from %s by %s.',
                        $this->member->name,
                        route('member', $this->member->getUrlParams()),
                        $this->member->clan_id,
                        $notifiable->name,
                        $removerName,
                    ),
                ],
                $reason,
                [
                    'name' => sprintf(
                        '%s / %s',
                        $notifiable->locality('platoon'),
                        $notifiable->locality('squad')
                    ),
                    'value' => $this->squad ? sprintf(
                        '%s / %s',
                        $this->squad->platoon->name,
                        $this->squad->name
                    ) : 'Unassigned',
                ],
                [
                    'name' => $handle?->label ?? 'In-Game Handle',
                    'value' => $handle?->pivot?->value ?? 'N/A',
                ],
            ], fn ($field) => $field !== null)))->error()
            ->send();
    }
}
