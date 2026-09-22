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

    private readonly string $counterpartDivision;

    private readonly string $type;

    private readonly bool $autoApproved;

    public function __construct(
        Member $member,
        string $counterpartDivision,
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
        $this->counterpartDivision = $counterpartDivision;
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

        /*
         * Filament's filter param names ("transferring_to"/"transferring_from") are relative
         * to $notifiable's own division: an INCOMING notice means members transferring TO this
         * division, OUTGOING means transferring FROM it.
         */
        $filterDirection = ($this->type === 'INCOMING') ? 'to' : 'from';

        /*
         * Message wording is relative to $counterpartDivision instead: an OUTGOING notice (sent
         * to the member's old division) describes where they went ("to"); an INCOMING notice
         * (sent to their new division) describes where they came from ("from"). Using
         * $filterDirection here was backward — it made the OLD division's channel read "has
         * transferred from {new division}", naming the division the member was leaving TO as if
         * it were where they came FROM.
         */
        $wordDirection = ($this->type === 'INCOMING') ? 'from' : 'to';

        $label = strtolower($this->type);

        /*
         * Non-officer transfers are auto-approved the instant they're submitted (see
         * MemberTransferController::store()) - there's nothing for leadership to approve or
         * deny, so this must read as a completed-transfer notice, not an action request. The
         * previous wording ("has been created... Manage transfer requests") was identical for
         * both cases and led an officer to believe they could still deny an auto-approved
         * transfer, which had already gone through.
         */
        if ($this->autoApproved) {
            $value = sprintf(
                ':white_check_mark: %s [%s] has transferred %s %s.',
                $this->member->present()->rankName(),
                $this->member->clan_id,
                $wordDirection,
                $this->counterpartDivision,
            );
        } else {
            $filters = [
                'filters[incomplete][isActive]'                   => 'true',
                "filters[transferring_{$filterDirection}][value]" => $divisionId,
            ];

            $manageUrl = route('filament.mod.resources.transfers.index') . '?' . http_build_query($filters);

            $value = sprintf(
                ':recycle: A transfer request for %s [%s] %s %s has been created. [Manage %s transfer requests](%s)',
                $this->member->present()->rankName(),
                $this->member->clan_id,
                $wordDirection,
                $this->counterpartDivision,
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
