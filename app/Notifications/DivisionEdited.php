<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class DivisionEdited extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $division;
    /**
     * @var
     */
    private $request;

    /**
     * Create a new notification instance.
     *
     * @param $division
     */
    public function __construct($division)
    {
        $this->division = $division;
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
     * @param $notifiable
     * @return array
     * @throws \Exception
     */
    public function toWebhook($notifiable)
    {
        $channel = str_slug($this->division->name) . '-officers';

        $authoringUser = auth()->check() ? auth()->user()->name : 'ClanAOD';

        return (new DiscordMessage())
            ->to($channel)
            ->message(":tools: **{$authoringUser}** updated division settings for **{$this->division->name}**")
            ->success()
            ->send();
    }

    public function toSlack()
    {
        $to = $this->division->settings()->get('slack_channel');
        if ($to) {
            $authoringUser = auth()->check() ? auth()->user()->name : 'ClanAOD';

            return (new SlackMessage())
                ->success()
                ->to($to)
                ->content("{$authoringUser} updated division settings for {$this->division->name}");
        }
    }
}
