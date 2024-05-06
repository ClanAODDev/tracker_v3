<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestHoldLifted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest)
    {
        $this->request = $memberRequest->with('member');
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
     * @return mixed
     *
     * @throws Exception
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('officer_channel');

        $message = addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: The hold placed on `{$this->request->member->name}` has been lifted. Your request will be processed soon.");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
