<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param bool $showClosed
     * @return \Illuminate\Http\Response
     */
    public function index($showClosed = false)
    {
        $tickets = $showClosed
            ? Ticket::closed()->with('type', 'caller', 'owner', 'division')->latest()->get()
            : Ticket::open()->with('type', 'caller', 'owner', 'division')->latest()->get();

        return view('help.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function show(Ticket $ticket)
    {
        return view('help.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param User $user
     * @param Ticket $ticket
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function ownTicket(Ticket $ticket)
    {
        $this->authorize('canAssignTickets', auth()->user());

        $ticket->ownTicketToMe();

        $this->showToast('Ticket has been assigned to you');

        return redirect()->route('tickets.show', $ticket);
    }
}
