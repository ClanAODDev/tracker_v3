<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\MemberAward;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotifyDivisionMemberAwardsBulk extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_awarded';

    private const MAX_FIELD_LENGTH = 1024;

    /**
     * @param  Collection<int, MemberAward>  $awards  Approved awards for a single division, eager-loaded with member and award.
     */
    public function __construct(private readonly Collection $awards) {}

    public function via($notifiable): array
    {
        return [BotChannel::class];
    }

    /**
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        $fields = $this->awards
            ->groupBy(fn (MemberAward $memberAward) => $memberAward->award->name)
            ->map(fn (Collection $awards, string $awardName) => [
                'name'  => $awardName,
                'value' => $this->summarizeRecipients($awards),
            ])
            ->values()
            ->all();

        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->message(sprintf(
                ':trophy: %d award%s approved!',
                $this->awards->count(),
                $this->awards->count() === 1 ? '' : 's'
            ))
            ->fields($fields)
            ->info()
            ->send();
    }

    private function summarizeRecipients(Collection $awards): string
    {
        $names = $awards->pluck('member.name')->join(', ');

        if (strlen($names) <= self::MAX_FIELD_LENGTH) {
            return $names;
        }

        return sprintf('%d members', $awards->count());
    }
}
