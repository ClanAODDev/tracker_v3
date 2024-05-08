<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotMessage;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewExternalRecruit extends Notification implements ShouldQueue
{
    use Queueable;

    private $member;

    private User $recruiter;

    /**
     * Create a new notification instance.
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

        return (new BotMessage())
            ->title($notifiable->name.' Division')
            ->thumbnail(getDivisionIconPath($notifiable->abbreviation))
            ->fields([
                [
                    'name' => '**EXTERNAL RECRUIT**',
                    'value' => addslashes("{$recruiter->name} from {$recruiter->member->division->name} just recruited " .
                        "`{$this->member->name}` into the {$notifiable->name} Division!"),
                ],
                [
                    'name' => 'View member profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])
            ->info()
            ->send();
    }
}
