<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Models\RankAction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PromotionPendingAcceptance extends Notification
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

    public function toBot($notifiable): array
    {
        $route = URL::temporarySignedRoute('promotion.confirm', now()->addMinutes(10), [
            $notifiable->clan_id,
            $this->action,
        ]);

        \Log::info($route);

        return (new BotDMMessage)
            ->to($notifiable->discord_id)
            ->message(sprintf(
                "**Congratulations!** You have received a promotion! Visit the following URL to accept or decline this promotion!\r\n\r\n%s",
                $route
            ))->send();
    }
}
