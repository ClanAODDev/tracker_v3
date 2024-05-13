<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
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
        return [BotChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return mixed
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail(getDivisionIconPath($notifiable->abbreviation))
            ->message(addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: A member status request for `{$this->member->name}` was put on hold by {$this->approver->name} for the following reason: `{$this->request->notes}`"))
            ->success()
            ->send();
    }
}
