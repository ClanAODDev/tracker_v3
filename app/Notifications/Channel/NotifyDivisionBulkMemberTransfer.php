<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class NotifyDivisionBulkMemberTransfer extends Notification implements ShouldQueue
{
    use DivisionSettableNotification, Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_transferred';

    public const TYPE_INCOMING = 'INCOMING';

    public const TYPE_OUTGOING = 'OUTGOING';

    /**
     * @param  Collection<int, Member>  $members
     */
    public function __construct(
        private readonly Collection $members,
        private readonly string $counterpartDivision,
        private readonly string $type,
    ) {
        $type = strtoupper($this->type);

        if (! in_array($type, [self::TYPE_INCOMING, self::TYPE_OUTGOING], true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid transfer type "%s"; must be "%s" or "%s".',
                $type,
                self::TYPE_INCOMING,
                self::TYPE_OUTGOING,
            ));
        }
    }

    public function via(): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        $direction = $this->type === self::TYPE_INCOMING ? 'in from' : 'out to';
        $names     = $this->members->map(fn ($member) => $member->present()->rankName())->implode(', ');
        $count     = $this->members->count();

        $value = sprintf(
            ':recycle: %d member%s transferred %s %s: %s.',
            $count,
            $count === 1 ? '' : 's',
            $direction,
            $this->counterpartDivision,
            $names,
        );

        return new BotChannelMessage($notifiable)
            ->title("{$notifiable->name} Division")
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name'  => '**BULK MEMBER TRANSFER**',
                    'value' => $value,
                ],
            ])
            ->info()
            ->send();
    }
}
