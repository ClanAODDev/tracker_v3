<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMemberRecruited extends Notification
{
    use Queueable;

    private $member;

    /**
     * Create a new notification instance.
     *
     * @param $division
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
        $channel = $notifiable->settings()->get('slack_channel');

        return (new DiscordMessage())
            ->success()
            ->to($channel)
            ->fields([
                [
                    'name' => '**NEW MEMBER RECRUITED**',
                    'value' => addslashes(':crossed_swords: ' . auth()->user()->name . " just recruited `{$this->member->name}` into the {$notifiable->name} Division!"),
                ],
                [
                    'name' => 'View Member Profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])->send();
    }
}
