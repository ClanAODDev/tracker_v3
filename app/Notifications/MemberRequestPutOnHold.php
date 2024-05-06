<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\Member;
use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestPutOnHold extends Notification implements ShouldQueue
{
    use Queueable;

    private $request;
    private Member $member;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest, Member $member)
    {
        $this->request = $memberRequest->with('member');
        $this->member = $member;
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
     * @param  mixed  $notifiable
     * @return mixed
     *
     * @throws Exception
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('officer_channel');

        $approver = auth()->user()->member;

        $message = addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: A member status request for `{$this->member->name}` was put on hold by {$approver->name} for the following reason: `{$this->request->notes}`");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
