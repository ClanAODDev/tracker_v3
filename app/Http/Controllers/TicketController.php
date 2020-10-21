<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
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
            ->allowedFilters(
                [
                    'caller.name',
                    'caller.member.clan_id',
                    'owner.name',
                    'owner.member.clan_id',
                    'state',
                    'type.slug',
                ]
            );

        $newCount = Ticket::new()->count();
        $assignedCount = Ticket::assigned()->count();
        $resolvedCount = Ticket::resolved()->count();

        return view('help.tickets.index', compact('tickets', 'newCount', 'assignedCount', 'resolvedCount'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  Ticket  $ticket
     * @return Ticket
     */
    public function show(Ticket $ticket)
    {
        return $ticket;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
