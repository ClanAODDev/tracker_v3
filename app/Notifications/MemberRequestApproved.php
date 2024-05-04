<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\MemberRequest;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestApproved extends Notification implements ShouldQueue
{
    use Queueable;

    private $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest, User $approver)
    {
        $this->request = $memberRequest;
        $this->approver = $approver;
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

        $message = addslashes("**MEMBER STATUS REQUEST** - :thumbsup: A member status request for `{$this->request->member->name}` was approved by {$this->approver->name}!");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
