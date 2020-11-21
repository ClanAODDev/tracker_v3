<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Notifications\AdminTicketUpdated;
use App\Notifications\NotifyAdminTicketCreated;
use App\Notifications\NotifyUserTicketCreated;
use Illuminate\Http\Request;
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function create()
    {
        if (!request()->get('type')) {
            return redirect(route('help.tickets.setup'));
        }

        $type = \App\Models\TicketType::whereSlug(request('type'))->first();

        return view('help.tickets.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_type' => 'required',
            'description' => 'string|min:25|required'
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
     * @param  Ticket  $ticket
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

        $ticket->notify(new AdminTicketUpdated(':white_check_mark: ' . $message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function reopen(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->reopen();

        $message = "Ticket has been reopened!";

        $this->showToast($message);

        $ticket->notify(new AdminTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function reject(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->reject();

        $message = "Ticket has been rejected";

        $this->showToast($message);

        $ticket->notify(new AdminTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function assignTo(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->ownTo(auth()->user());

        // check if auth user is not the assigned user, in which case alert the newly assigned user

        $message = "Ticket has been assigned to " . auth()->user()->name;

        $this->showToast($message);

        $ticket->notify(new AdminTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }

    public function selfAssign(Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $ticket->ownTo(auth()->user());

        $message = "Ticket has been assigned to " . auth()->user()->name;

        $this->showToast($message);

        $ticket->notify(new AdminTicketUpdated($message));

        return redirect(route('help.tickets.show', $ticket));
    }
}
