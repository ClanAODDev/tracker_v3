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
            ->thumbnail(getDivisionIconPath($notifiable->abbreviation))
            ->fields([
                [
                    'name' => '**MEMBER REMOVED**',
                    'value' => addslashes(":door: {$this->member->name} [{$this->member->clan_id}] was removed from {$notifiable->name} by " . $remover->name),
                ],
                [
                    'name' => 'Reason',
                    'value' => addslashes($this->removalReason),
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])->error()
            ->send();
    }
}
