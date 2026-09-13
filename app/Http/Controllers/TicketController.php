<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\TicketSerializer;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $user           = $request->user();
        $canWorkTickets = TicketType::get()->contains(fn ($type) => $type->userCanWork($user));

        $view = $request->query('view');
        if (! $canWorkTickets || ! in_array($view, ['assigned', 'all'], true)) {
            $view = 'user';
        }

        if ($view === 'user') {
            $tickets = Ticket::where('caller_id', $user->id)
                ->with(['type', 'owner.member', 'division'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($ticket) => TicketSerializer::ticket($ticket))
                ->values();
        } else {
            $tickets = Ticket::with(['type', 'owner.member', 'division', 'caller.member'])
                ->orderByDesc('created_at')
                ->get()
                ->filter(fn ($ticket) => $ticket->type?->userCanWork($user) ?? $user->isRole('admin'))
                ->map(fn ($ticket) => TicketSerializer::ticket($ticket, includeCaller: true))
                ->values();
        }

        return Inertia::render('tickets/index', [
            'view'           => $view,
            'canWorkTickets' => $canWorkTickets,
            'currentUserId'  => $user->id,
            'ticketTypes'    => TicketSerializer::types($user),
            'tickets'        => $tickets,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('tickets/create', [
            'ticketTypes' => TicketSerializer::types($request->user()),
        ]);
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $user    = $request->user();
        $canWork = $ticket->type?->userCanWork($user) ?? $user->isRole('admin');

        abort_if($ticket->caller_id !== $user->id && ! $canWork, 403);

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return Inertia::render('tickets/show', [
            'ticket'        => TicketSerializer::ticket($ticket, includeComments: true, includeCaller: true),
            'canWork'       => $canWork,
            'currentUserId' => $user->id,
            'workers'       => $canWork
                ? TicketSerializer::workers($ticket->type?->id, excludeUserId: $user->id)
                : [],
        ]);
    }
}
