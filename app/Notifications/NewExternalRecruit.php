<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
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
        return [WebhookChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('officer_channel');

        $recruiter = $this->recruiter;

        return (new DiscordMessage())
            ->error()
            ->to($channel)
            ->fields([
                [
                    'name' => '**EXTERNAL RECRUIT**',
                    'value' => addslashes("{$recruiter->name} from {$recruiter->member->division->name} just recruited 
                    `{$this->member->name}` into the {$notifiable->name} Division!"),
                ],
                [
                    'name' => 'View member profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])
            ->send();
    }
}
