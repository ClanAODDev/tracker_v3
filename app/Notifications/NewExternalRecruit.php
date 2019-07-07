<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewExternalRecruit extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $user;

    /**
     * @var
     */
    private $member;

    /**
     * Create a new notification instance.
     *
     * @param $member
     * @param $division
     */
    public function __construct($member, $division)
    {
        $this->division = $division;
        $this->member = $member;
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
     * @return array
     * @throws Exception
     */
    public function toWebhook()
    {
        $channel = $this->division->settings()->get('slack_channel');

        $user = auth()->user();

        return (new DiscordMessage())
            ->error()
            ->to($channel)
            ->fields([
                [
                    'name' => '**EXTERNAL RECRUIT**',
                    'value' => "{$user->name} from {$user->member->division->name} just recruited `{$this->member->name}` into the {$this->division->name} Division!"
                ],
                [
                    'name' => 'View member profile',
                    'value' => route('member', $this->member->getUrlParams())
                ]
            ])
            ->send();
    }
}
