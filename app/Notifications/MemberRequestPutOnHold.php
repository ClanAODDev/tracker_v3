<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use App\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberRequestPutOnHold extends Notification
{
    use Queueable;


    private $request;

    /**
     * Create a new notification instance.
     *
     * @param $memberRequest
     */
    public function __construct(MemberRequest $memberRequest)
    {
        $this->request = $memberRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function toWebhook()
    {
        $division = $this->request->division;

        $channel = $division->settings()->get('slack_channel');

        $approver = auth()->user()->member;

        $message = addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: A member status request for `{$this->request->member->name}` was put on hold by {$approver->name} for the following reason: `{$this->request->notes}`");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
