<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberTransferred extends Notification implements ShouldQueue
{
    use Queueable;

    private Member $member;

    private Division $destinationDivision;

    /**
     * Create a new notification instance.
     */
    public function __construct(Member $member, Division $destinationDivision)
    {
        $this->member = $member;
        $this->destinationDivision = $destinationDivision;
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
        return (new BotChannelMessage($notifiable))
            ->title($this->destinationDivision->name . ' Division')
            ->target($notifiable->settings()->get('voice_alert_member_transferred'))
            ->thumbnail($this->destinationDivision->getLogoPath())
            ->fields([
                [
                    'name' => '**MEMBER TRANSFER**',
                    'value' => addslashes(":recycle: {$this->member->name} [{$this->member->clan_id}] transferred to {$this->destinationDivision->name}"),
                ],
            ])->info()
            ->send();
    }
}
