<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Notifications\Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionMemberRequestHoldLifted extends Notification implements ShouldQueue
{
    use Queueable;

    private MemberRequest $request;

    private Member $member;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest, Member $member)
    {
        $this->request = $memberRequest;
        $this->member = $member;
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
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->message(addslashes("**MEMBER STATUS REQUEST ON HOLD** - :hourglass: The hold placed on `{$this->member->name}` has been lifted. Your request will be processed soon."))
            ->success()
            ->send();
    }
}
