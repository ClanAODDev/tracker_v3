<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\Recommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberRecommendationSubmitted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Recommendation $recommendation)
    {
        $this->recommendation = $recommendation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
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
        // @TODO: Rename to discord channel
        $channel = $notifiable->settings()->get('slack_channel');

        $string = 'A member recommendation was submitted for';

        return (new DiscordMessage())
            ->to($channel)
            ->message(addslashes(":military_medal: {$string} `{$this->recommendation->member->name}`"))
            ->success()
            ->send();
    }
}
