<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Notifications\React\TicketReaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

#[Tries(20)]
#[MaxExceptions(3)]
#[Backoff([30, 120, 300])]
class DeferredTicketReact implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public readonly string $status
    ) {}

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
