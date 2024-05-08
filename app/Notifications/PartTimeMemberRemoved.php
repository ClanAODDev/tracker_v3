<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PartTimeMemberRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;

    private $member;

    /**
     * Create a new notification instance.
     *
     * @param  $partTimeDivision
     */
    public function __construct($member)
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
     * @return array
     *
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        $primaryDivision = $this->member->division;

        return (new BotMessage())
            ->title($primaryDivision->name.' Division')
            ->thumbnail(getDivisionIconPath($primaryDivision->abbreviation))
            ->fields([
                [
                    'name' => '**PART TIME MEMBER REMOVED**',
                    'value' => addslashes(":door: {$this->member->name} [{$this->member->clan_id}] was removed from {$primaryDivision->name}, and they were a part-time member in your division"),
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])->error()
            ->send();
    }
}
