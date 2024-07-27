<?php

// @TODO

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestDenied extends Notification implements ShouldQueue
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
     * @return array
     */
    public function via()
    {
        return [BotChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return mixed
     */
    public function toBot($notifiable)
    {
        $notes = addslashes($this->request->notes);

        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => '**MEMBER STATUS REQUEST**',
                    'value' => addslashes(":skull_crossbones: A member status request for `{$this->member->name}` was denied."),
                ],
                [
                    'name' => 'The reason for the denial was:',
                    'value' => "`{$notes}`",
                ],
                [
                    'name' => 'Manage member requests',
                    'value' => route('division.member-requests.index', $notifiable),
                ],
            ])
            ->success()
            ->send();
    }
}
