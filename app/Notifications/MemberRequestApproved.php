<?php

namespace App\Notifications;

use App\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
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
        $to = ($this->request->division->settings()->get('slack_channel')) ?: '@' . $this->request->requester->name;

        $approver = auth()->user()->member;

        $message = "*MEMBER STATUS REQUEST*\nA member status request for `{$this->request->member->name}` was approved by {$approver->name}!";

        return (new SlackMessage())
            ->success()
            ->to($to)
            ->content($message);
    }
}
