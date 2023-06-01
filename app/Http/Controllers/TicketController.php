<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\NotifyAdminTicketCreated;
use App\Notifications\NotifyCallerTicketUpdated;
use App\Notifications\NotifyNewTicketOwner;
use App\Notifications\NotifyUserTicketCreated;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Spatie\QueryBuilder\QueryBuilder;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function setup()
    {
        $ticketTypes = TicketType::orderBy('display_order', 'ASC')
            ->get();

        $ticketTypes = $ticketTypes->filter(function ($type) {
            return collect(json_decode($type->role_access))->contains(auth()->user()->role->value)
                || empty(json_decode($type->role_access));
        });

        return view('help.tickets.setup', compact('ticketTypes'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = QueryBuilder::for(Ticket::class)
            ->allowedFilters([
                'type.slug',
                'caller.name',
                'caller.member.clan_id',
                'owner.name',
                'owner.member.clan_id',
                'state',
                'description',
            ]);

        // filter tickets unless you're an admin
        if (!auth()->user()->can('manage', Ticket::class)) {
            $tickets = $tickets->whereCallerId(auth()->id());
        }

        if (request('search-filter') && request('search-criteria') && request()->isMethod('get')) {
            return redirect(
                route('help.tickets.index')
                . '?' . request('search-query')
                . '&filter[' . request('search-filter') . ']=' . request('search-criteria')
            );
        }

        return view('help.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Redirector|RedirectResponse|Response
     */
    public function create()
    {
        if (!request()->get('type')) {
            return redirect(route('help.tickets.setup'));
        }

        $type = TicketType::whereSlug(request('type'))->first();

        return view('help.tickets.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Application|Redirector|RedirectResponse|Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_type' => 'required',
            'description' => 'string|min:25|required',
        ]);

        $ticket = new Ticket();
        $ticket->state = 'new';
        $ticket->ticket_type_id = $validated['ticket_type'];
        $ticket->description = $validated['description'];
        $ticket->caller_id = auth()->id();
        $ticket->division_id = auth()->user()->member->division_id;
        $ticket->save();

        // send a message to admin channel as well as to the caller
        $ticket->notify(new NotifyUserTicketCreated());
        $ticket->notify(new NotifyAdminTicketCreated());

        flash('Your ticket has been created! Please allow 24/48 hours for a response from an admin.')->important();

        return redirect(route('help.tickets.show', $ticket));
    }

    /**
     * Display the specified resource.
     *
     * @return Ticket
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return view('help.tickets.show', compact('ticket'));
    }

    public function resolve(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->resolve();

        $message = "Ticket `{$ticket->type->name}` has been resolved";

        $this->showToast($message);

        $ticket->notify(new NotifyCallerTicketUpdated(':white_check_mark: ' . $message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function reopen(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->reopen();

        $message = 'Ticket has been reopened!';

        $this->showToast($message);

        $ticket->notify(new NotifyCallerTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function reject(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->reject();

        $message = 'Ticket has been rejected';

        $this->showToast($message);

        $ticket->notify(new NotifyCallerTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function assignTo(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $validated = request()->validate(['owner_id' => 'required|exists:users,id']);

        $assignedUser = User::find($validated['owner_id']);

        $ticket->ownTo($assignedUser);

        $message = 'Ticket has been assigned to ' . $assignedUser->name;

        $this->showToast($message);

        $ticket->notify(new NotifyCallerTicketUpdated($message));
        $ticket->notify(new NotifyNewTicketOwner($assignedUser, auth()->user()));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function selfAssign(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->ownTo(auth()->user());

        $message = 'Ticket has been assigned to ' . auth()->user()->name;

        $this->showToast($message);

        $ticket->notify(new NotifyCallerTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }
}
