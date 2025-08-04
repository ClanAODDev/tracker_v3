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
use InvalidArgumentException;

class NotifyDivisionMemberTransferRequested extends Notification implements ShouldQueue
{
    use DivisionSettableNotification, Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_transferred';

    /**
     * Create a new notification instance.
     */
    public const TYPE_INCOMING = 'INCOMING';

    public const TYPE_OUTGOING = 'OUTGOING';

    private readonly Member $member;

    private readonly string $destinationDivision;

    private readonly string $type;

    public function __construct(
        Member $member,
        string $destinationDivision,
        string $type,
    ) {
        $type = strtoupper($type);

        if (! in_array($type, [self::TYPE_INCOMING, self::TYPE_OUTGOING], true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid transfer type "%s"; must be "%s" or "%s".',
                $type,
                self::TYPE_INCOMING,
                self::TYPE_OUTGOING,
            ));
        }

        $this->member = $member;
        $this->destinationDivision = $destinationDivision;
        $this->type = $type;
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
     * @return array[]
     *
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        $divisionId = $notifiable->id;

        $filters = [
            'tableFilters[incomplete][isActive]' => 'true',
        ];

        $direction = ($this->type === 'INCOMING') ? 'to' : 'from';
        $filters["tableFilters[transferring_{$direction}][value]"] = $divisionId;

        $label = strtolower($this->type);

        $queryString = '?' . http_build_query($filters);

        $baseUrl = route('filament.mod.resources.transfers.index');
        $manageUrl = $baseUrl . $queryString;

        return (new BotChannelMessage($notifiable))
            ->title("{$notifiable->name} Division")
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => '**MEMBER TRANSFER REQUEST**',
                    'value' => sprintf(
                        ':recycle: A transfer request for %s [%s] to %s has been created. [Manage %s transfer requests](%s)',
                        $this->member->name,
                        $this->member->clan_id,
                        $this->destinationDivision,
                        $label,
                        $manageUrl,
                    ),
                ],
            ])
            ->info()
            ->send();
    }
}
