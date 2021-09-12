<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberRequestHoldLifted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest)
    {
        $this->request = $memberRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function toWebhook()
    {
        $division = $this->request->division;

        $channel = $division->settings()->get('slack_channel');

        $message = addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: The hold placed on `{$this->request->member->name}` has been lifted. Your request will be processed soon.");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
