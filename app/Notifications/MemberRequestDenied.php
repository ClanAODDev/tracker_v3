<?php

// @TODO

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use App\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberRequestDenied extends Notification
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
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    /**
     * @return mixed
     */
    public function toWebhook()
    {
        $division = $this->request->division;

        $channel = str_slug($division->name) . '-officers';

        $path = route('division.member-requests.index', $this->request->division);

        return (new DiscordMessage())
            ->error()
            ->to($channel)
            ->fields([
                [
                    'name' => "**MEMBER STATUS REQUEST**",
                    'value' => ":skull_crossbones: A member status request for `{$this->request->member->name}` was denied."
                ],
                [
                    'name' => 'The reason for the denial was:',
                    'value' => "{$this->request->notes} - [View Member Requests]({$path})"
                ]
            ])
            ->send();
    }
}
