<?php

namespace App\Notifications;

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
        return ['slack'];
    }

    public function toSlack()
    {
        $to = ($this->division->settings()->get('slack_channel'))
            ?: '@' . auth()->user()->name;

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content(auth()->user()->name . " updated division settings for " . $this->division->name);
    }
}
