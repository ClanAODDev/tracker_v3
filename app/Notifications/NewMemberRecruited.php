<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewMemberRecruited extends Notification implements ShouldQueue
{
    use Queueable;

    private $member;

    private User $recruiter;

    /**
     * Create a new notification instance.
     *
     * @param  $division
     */
    public function __construct($member, User $recruiter)
    {
        $this->member = $member;
        $this->recruiter = $recruiter;
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
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        $recruiter = $this->recruiter;
        $handle = $this->member->handles->filter(fn ($handle) => $handle->id === $this->member->division->handle_id)->first();

        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get('chat_alerts.member_created'))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => ':crossed_swords: New Member Recruited',
                    'value' => sprintf(
                        "%s just recruited [{$this->member->name}](%s) into the {$notifiable->name} Division!",
                        $recruiter->name,
                        route('member', $this->member->getUrlParams())
                    ),
                ],
                [
                    'name' => sprintf(
                        '%s / %s',
                        $this->member->division->locality('platoon'),
                        $this->member->division->locality('squad')
                    ),
                    'value' => $this->member->squad ? sprintf(
                        '%s / %s',
                        $this->member->platoon->name,
                        $this->member->squad->name
                    ) : 'Unassigned',
                ],
                [
                    'name' => $handle->label ?? 'In-Game Handle',
                    'value' => $handle->pivot->value ?? 'N/A',
                ],
            ])
            ->success()
            ->send();
    }
}
