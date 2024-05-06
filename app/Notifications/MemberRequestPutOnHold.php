<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestPutOnHold extends Notification implements ShouldQueue
{
    use Queueable;

    private MemberRequest $request;
    private Member $member;
    private User $approver;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest, User $approver, Member $member)
    {
        $this->request = $memberRequest;
        $this->approver = $approver;
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

        $message = addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: A member status request for `{$this->member->name}` was put on hold by {$this->approver->name} for the following reason: `{$this->request->notes}`");

        return (new DiscordMessage())
            ->to($channel)
            ->message($message)
            ->success()
            ->send();
    }
}
