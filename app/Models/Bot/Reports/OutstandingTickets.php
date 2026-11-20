<?php

namespace App\Models\Bot\Reports;

use App\Enums\Rank;
use App\Models\Bot\Report;
use App\Models\Member;
use App\Models\Ticket;

class OutstandingTickets implements Report
{
    private const MAX_LINES = 30;

    public function handle(): array
    {
        if (! $this->callerIsMasterSergeantOrAbove()) {
            return ['message' => 'This report is limited to MSgt and above.'];
        }

        $tickets = Ticket::query()
            ->whereIn('state', ['new', 'assigned'])
            ->orderBy('created_at')
            ->get();

        if ($tickets->isEmpty()) {
            return ['message' => 'No outstanding tickets.'];
        }

        $lines = $tickets->take(self::MAX_LINES)->map(fn (Ticket $ticket) => sprintf(
            '[#%d](%s) **%s** · %s · <t:%d:R>',
            $ticket->id,
            route('help.tickets.show', $ticket),
            $ticket->type?->name ?? 'Ticket',
            $ticket->owner?->name ?? '*Unassigned*',
            $ticket->created_at->timestamp,
        ));

        if ($tickets->count() > self::MAX_LINES) {
            $lines->push(sprintf('…and %d more', $tickets->count() - self::MAX_LINES));
        }

        return [
            'embed' => [
                'color'       => 10181046,
                'title'       => "Outstanding tickets ({$tickets->count()})",
                'url'         => route('help.tickets.widget', ['view' => 'all']),
                'description' => $lines->implode("\n"),
            ],
        ];
    }

    private function callerIsMasterSergeantOrAbove(): bool
    {
        $discordId = request()->string('discord_id')->toString();

        if ($discordId === '') {
            return false;
        }

        $member = Member::where('discord_id', $discordId)
            ->where('division_id', '>', 0)
            ->first();

        return $member !== null && $member->rank->isAtLeast(Rank::MASTER_SERGEANT);
    }
}
