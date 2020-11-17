@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin Support
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            All Tickets
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div style="display: flex; justify-content: space-between">
            @can('manage', \App\Models\Ticket::class)
                @if(request('filter') && is_array(request('filter')))
                    <div>
                        <h4 class="text-uppercase"><i class="fas fa-filter text-accent"></i> Active Filters</h4>
                        <a href="{{ route('help.tickets.index') }}" class="btn btn-danger btn-rounded"><i
                                class="fa fa-trash"></i> Reset</a>
                        @foreach (request('filter') as $attribute => $filter)
                            @if ($attribute && $filter)
                                <a class="btn btn-default btn-rounded hover-strikethrough" title="Click to remove"
                                   href="{{ urldecode(remove_query_params(["filter[{$attribute}]"])) }}">
                                    {{ sanitize_filter_attribute($attribute) }} = <code>{{ $filter }}</code>
                                </a>
                            @endif
                        @endforeacH
                    </div>
                @endif
            @endcan
            <div>
                <h4 class="text-uppercase"><i class="fas fa-search text-accent"></i> Show only</h4>
                <small class="text-uppercase">
                    <a href="?filter[state]=new"
                       class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'new' ? 'active' : '' }}">
                        <i class="fa fa-asterisk text-info"></i> New</a>
                    <a href="?filter[state]=assigned"
                       class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'assigned' ? 'active' : '' }}">
                        <i class="fa fa-hourglass-half text-accent"></i> Assigned</a>
                    <a href="?filter[state]=resolved"
                       class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'resolved' ? 'active' : '' }}">
                        <i class="fa fa-check-circle text-success"></i> Resolved</a>
                    <a href="?filter[state]=resolved"
                       class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'rejected' ? 'active' : '' }}">
                        <i class="fa fa-times text-danger"></i> Rejected</a>

                </small>
            </div>
        </div>

        @can('manage', \App\Models\Ticket::class)
            <hr>
            <form>
                <div class="row">
                    <form action="">
                        <input type="hidden" name="search-query"
                               value="{{ urldecode(http_build_query(request()->query())) }}">
                        <div class="col-md-3">
                            <select name="search-filter" id="" class="form-control">
                                <option value="" hidden disabled selected required>Select a filter</option>
                                <option value="description">Ticket Description</option>
                                <option value="type.slug">Type</option>
                                <option value="caller.name">Caller Name</option>
                                <option value="caller.member.clan_id">Caller Clan ID</option>
                                <option value="owner.name">Owner Name</option>
                                <option value="owner.member.clan_id">Owner Clan ID</option>
                                <option value="state">State (new, assigned, resolved)</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" placeholder="Search criteria" name="search-criteria"
                                   required>
                        </div>
                    </form>
                </div>
            </form>
        @endcan

        <hr>

        @unless($tickets->count())
            <p>
                <i class="fas fa-exclamation-circle"></i>
                @can('manage', \App\Models\Ticket::class)
                    No tickets match the provided criteria. Please check your active filters.
                @else
                    You don't currently have any tickets. <a href="{{ route('help.tickets.setup') }}">Create one</a>?
                @endcan
            </p>
        @else

            <div class="panel panel-filled">
                <div class="table-responsive">
                    <table class="table table-hover basic-datatable">
                        <thead>
                        <tr>
                            <th class="no-sort"></th>
                            <th class="hidden-sm hidden-xs">Type</th>
                            <th>Caller</th>
                            <th>State</th>
                            <th class="hidden-sm hidden-xs">Owned By</th>
                            <th class="hidden-sm hidden-xs">Updated At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($tickets->get() as $ticket)
                            <tr>
                                <td><a href="{{ route('help.tickets.show', $ticket) }}"
                                       class="btn btn-squared btn-block btn-primary">#{{ $ticket->id }}</a></td>
                                <td class="hidden-sm hidden-xs">{{ $ticket->type->name }}</td>
                                <td>
                                    <a href="?filter[caller.name]={{ $ticket->caller->name }}">{{ $ticket->caller->name }}</a>
                                </td>
                                <td class="text-center">
                                    <a title="Show only {{ $ticket->state }} tickets"
                                       href="{{ "?filter[state]={$ticket->state}" }}"
                                       class="text-{{ $ticket->stateColor }} text-uppercase btn btn-rounded btn-{{ $ticket->stateColor }}">{{ $ticket->state }}</a>
                                </td>
                                <td class="hidden-sm hidden-xs">
                                    @if($ticket->owner)
                                        <a href="?filter[owner.name]={{ $ticket->owner->name }}">{{ $ticket->owner->name }}</a>
                                    @else
                                        <span class="text-muted">UNASSIGNED</span>
                                    @endif
                                </td>
                                <td class="hidden-sm hidden-xs">{{ $ticket->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


        @endunless
    </div>
@stop
