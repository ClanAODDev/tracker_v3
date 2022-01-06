<?php

// @TODO

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\MemberRequest;
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
     * @return array
     */
    public function via()
    {
        return [WebhookChannel::class];
    }

    /**
     * @param mixed $notifiable
     *
     * @return mixed
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('slack_channel');

        $notes = addslashes($this->request->notes);

        return (new DiscordMessage())
            ->error()
            ->to($channel)
            ->fields([
                [
                    'name'  => '**MEMBER STATUS REQUEST**',
                    'value' => addslashes(":skull_crossbones: A member status request for `{$this->request->member->name}` was denied."),
                ],
                [
                    'name'  => 'The reason for the denial was:',
                    'value' => "`{$notes}`",
                ],
                [
                    'name'  => 'Manage member requests',
                    'value' => route('division.member-requests.index', $notifiable),
                ],
            ])->send();
    }
}
