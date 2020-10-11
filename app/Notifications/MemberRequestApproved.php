<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\MemberRequest;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberRequestApproved extends Notification
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

        $message = addslashes("**MEMBER STATUS REQUEST** - :thumbsup: A member status request for `{$this->request->member->name}` was approved by {$approver->name}!");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
