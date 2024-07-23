<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberNameChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private $names;

    public function __construct($names)
    {
        $this->names = $names;
    }

    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail($notifiable->getLogoPath())
            ->message(addslashes(":tools: **MEMBER STATUS - NAME CHANGE**\n`{$this->names['oldName']}` is now known as `{$this->names['newName']}`. Please inform the member of this change."))
            ->success()
            ->send();
    }
}
