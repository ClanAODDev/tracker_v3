<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Models\RankAction;
use App\Notifications\Channel\NotifyDivisionFailedPromotionAcceptance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Log;

class NotifyMemberPromotionPendingAcceptance extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly RankAction $action) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    public function failed(): void
    {
        $this->action->member->division->notify(
            new NotifyDivisionFailedPromotionAcceptance($this->action->member)
        );
    }

    public function toBot($notifiable): array
    {
        $route = URL::temporarySignedRoute('promotion.confirm', now()->addMinutes(
            config('aod.rank.promotion_acceptance_mins')
        ), [
            $notifiable->clan_id,
            $this->action,
        ]);

        Log::info($route);

        return new BotDMMessage()
            ->to($notifiable->discord_id)
            ->message(sprintf(
                "**Congratulations!** You have received a promotion! Visit the following URL to accept or decline this promotion!\r\n\r\n[View Promotion](%s)",
                $route
            ))->send();
    }
}
