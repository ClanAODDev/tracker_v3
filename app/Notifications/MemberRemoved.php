<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;

    private $member;

    private $remover;

    private $removalReason;

    /**
     * Create a new notification instance.
     */
    public function __construct($member, User $remover, $removalReason)
    {
        $this->member = $member;
        $this->remover = $remover;
        $this->removalReason = $removalReason;
    }

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
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        $remover = $this->remover;

        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail($notifiable->getLogoPath())->fields([
                [
                    'name' => 'Member Removed',
                    'value' => sprintf(
                        ':door: [%s](%s) [%d] was removed from %s by %s.',
                        $this->member->name,
                        route('member', $this->member->getUrlParams()),
                        $this->member->clan_id,
                        $notifiable->name,
                        $remover->name,
                    ),
                ], [
                    'name' => 'Reason',
                    'value' => $this->removalReason,
                ],
            ])->error()
            ->send();
    }
}
