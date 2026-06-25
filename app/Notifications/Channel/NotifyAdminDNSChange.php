<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminDNSChange extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly array $created,
        private readonly array $deleted,
        private readonly string $domain,
    ) {}

    public function via($notifiable): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable): array
    {
        $lines = [];

        if ($this->created) {
            $fqdns   = implode(', ', array_map(fn ($s) => "`{$s}.{$this->domain}`", $this->created));
            $lines[] = "**Created:** {$fqdns}";
        }

        if ($this->deleted) {
            $fqdns   = implode(', ', array_map(fn ($s) => "`{$s}.{$this->domain}`", $this->deleted));
            $lines[] = "**Deleted:** {$fqdns}";
        }

        return (new BotChannelMessage($notifiable))
            ->title('DNS Sync')
            ->target('it_team')
            ->message(implode("\n", $lines))
            ->info()
            ->send();
    }
}
