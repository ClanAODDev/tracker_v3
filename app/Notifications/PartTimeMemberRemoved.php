<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PartTimeMemberRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;

    private $member;

    private $primaryDivision;

    private $removalReason;

    /**
     * Create a new notification instance.
     *
     * @param  $partTimeDivision
     */
    public function __construct($member, $removalReason)
    {
        $this->member = $member;
        $this->primaryDivision = $this->member->division;
        $this->removalReason = $removalReason;
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
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($this->primaryDivision->name . ' Division')
            ->thumbnail($this->primaryDivision->getLogoPath())
            ->fields([
                [
                    'name' => '**PART TIME MEMBER REMOVED**',
                    'value' => addslashes(":door: {$this->member->name} [{$this->member->clan_id}] was removed from {$this->primaryDivision->name}, and they were a part-time member in your division"),
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
