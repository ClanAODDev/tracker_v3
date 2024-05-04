<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
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

    /**
     * Create a new notification instance.
     */
    public function __construct($member, User $remover)
    {
        $this->member = $member;
        $this->remover = $remover;
    }

    /**
     * Get the notification's delivery channels.
     *
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
     * @throws \Exception
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('officer_channel');

        $remover = $this->remover;

        return (new DiscordMessage())
            ->info()
            ->to($channel)
            ->fields([
                [
                    'name' => '**MEMBER REMOVED**',
                    'value' => addslashes(":door: {$this->member->name} [{$this->member->clan_id}] was removed from {$notifiable->name} by " . $remover->name),
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])->send();
    }
}
