<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;
use Exception;
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

    private readonly bool $autoApproved;

    public function __construct(
        Member $member,
        string $destinationDivision,
        string $type,
        bool $autoApproved = false,
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

        $this->member              = $member;
        $this->destinationDivision = $destinationDivision;
        $this->type                = $type;
        $this->autoApproved        = $autoApproved;
    }

    public function isAutoApproved(): bool
    {
        return $this->autoApproved;
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
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        $divisionId = $notifiable->id;

        $direction = ($this->type === 'INCOMING') ? 'to' : 'from';
        $label     = strtolower($this->type);

        // Non-officer transfers are auto-approved the instant they're submitted (see
        // MemberTransferController::store()) - there's nothing for leadership to approve or
        // deny, so this must read as a completed-transfer notice, not an action request. The
        // previous wording ("has been created... Manage transfer requests") was identical for
        // both cases and led an officer to believe they could still deny an auto-approved
        // transfer, which had already gone through.
        if ($this->autoApproved) {
            $value = sprintf(
                ':white_check_mark: %s [%s] has transferred %s %s.',
                $this->member->present()->rankName(),
                $this->member->clan_id,
                $direction,
                $this->destinationDivision,
            );
        } else {
            $filters = [
                'filters[incomplete][isActive]'             => 'true',
                "filters[transferring_{$direction}][value]" => $divisionId,
            ];

            $manageUrl = route('filament.mod.resources.transfers.index') . '?' . http_build_query($filters);

            $value = sprintf(
                ':recycle: A transfer request for %s [%s] to %s has been created. [Manage %s transfer requests](%s)',
                $this->member->present()->rankName(),
                $this->member->clan_id,
                $this->destinationDivision,
                $label,
                $manageUrl,
            );
        }

        return new BotChannelMessage($notifiable)
            ->title("{$notifiable->name} Division")
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name'  => $this->autoApproved ? '**MEMBER TRANSFER**' : '**MEMBER TRANSFER REQUEST**',
                    'value' => $value,
                ],
            ])
            ->info()
            ->send();
    }
}
