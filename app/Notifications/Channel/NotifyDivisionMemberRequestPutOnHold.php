<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\User;
use App\Notifications\Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionMemberRequestPutOnHold extends Notification implements ShouldQueue
{
    use Queueable;

    private MemberRequest $request;

    private Member $member;

    private User $holder;

    /**
     * Create a new notification instance.
     */
    public function __construct(MemberRequest $memberRequest, User $holder, Member $member)
    {
        $this->request = $memberRequest;
        $this->holder = $holder;
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
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => sprintf(
                        'A member status request for %s was put on hold by %s.',
                        $this->member->name,
                        $this->holder->name,
                    ),
                    'value' => 'Reason: ' . $this->request->notes,
                ],
            ])
            ->warning()
            ->send();
    }
}
