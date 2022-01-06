<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewExternalRecruit extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param $member
     */
    public function __construct(private $member)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
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
     * @throws Exception
     *
     * @return array
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('slack_channel');

        $user = auth()->user();

        return (new DiscordMessage())
            ->error()
            ->to($channel)
            ->fields([
                [
                    'name'  => '**EXTERNAL RECRUIT**',
                    'value' => addslashes("{$user->name} from {$user->member->division->name} just recruited `{$this->member->name}` into the {$notifiable->name} Division!"),
                ],
                [
                    'name'  => 'View member profile',
                    'value' => route('member', $this->member->getUrlParams()),
                ],
            ])
            ->send();
    }
}
