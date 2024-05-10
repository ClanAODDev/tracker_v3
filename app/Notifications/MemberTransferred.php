<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotMessage;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberTransferred extends Notification implements ShouldQueue
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
        return [BotChannel::class];
    }

    /**
     * @return array[]
     *
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        return (new BotMessage())
            ->title($notifiable->name . ' Division')
            ->thumbnail(getDivisionIconPath($notifiable->abbreviation))
            ->fields([
                [
                    'name' => '**MEMBER TRANSFER**',
                    'value' => addslashes(":recycle: {$this->member->name} [{$this->member->clan_id}] transferred to {$notifiable->name}"),
                ],
            ])->info()
            ->send();
    }
}
