<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
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
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * @return mixed
     */
    public function toSlack()
    {
        $user = auth()->user();

        $to = ($this->division->settings()->get('slack_channel'))
            ?: '@' . $user->name;

        $message = "*EXTERNAL RECRUIT*\n{$user->name} from {$user->member->division->name} just recruited `{$this->member->name}` into the {$this->division->name} Division!";

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content($message)
            ->attachment(function ($attachment) {
                $attachment->title('View Member Profile')
                    ->content(
                        route('member', $this->member->clan_id)
                    );
            });
    }
}
