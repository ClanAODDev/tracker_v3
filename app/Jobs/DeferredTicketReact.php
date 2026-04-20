<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Notifications\React\TicketReaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeferredTicketReact implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public int $tries = 20;

    public int $maxExceptions = 3;

    public function __construct(
        private Ticket $ticket,
        private readonly string $status
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $ticket = $this->ticket->fresh();

        if (empty($ticket->external_message_id)) {
            Log::info('DeferredTicketReact: message not yet posted, releasing', [
                'ticket_id' => $ticket->id,
                'attempt'   => $this->attempts(),
            ]);
            $this->release(15);

            return;
        }

        $ticket->notifyNow(new TicketReaction($this->status));
    }
}
