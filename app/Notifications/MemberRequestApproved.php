<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotMessage;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestApproved extends Notification implements ShouldQueue
{
    use Queueable;

    private $request;
    private User $approver;
    private Member $member;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest, User $approver, Member $member)
    {
        $this->request = $memberRequest;
        $this->member = $member;
        $this->approver = $approver;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
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
        return (new BotMessage())
            ->title($notifiable->name.' Division')
            ->thumbnail(getDivisionIconPath($notifiable->abbreviation))
            ->message(addslashes("**MEMBER STATUS REQUEST** - :thumbsup: A member status request for `{$this->member->name}` was approved by {$this->approver->name}!"))
            ->success()
            ->send();
    }
}
