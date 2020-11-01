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
            Ticket Index
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div style="display: flex; justify-content: space-between">
            @if(request('filter') && is_array(request('filter')))
                <div>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('help.tickets.index') }}" class="btn btn-danger btn-rounded"><i class="fa fa-times"></i> Reset</a>
                            @foreach (request('filter') as $attribute => $filter)
                                @if ($attribute && $filter)
                                    <a class="btn btn-default btn-rounded hover-strikethrough" title="Click to remove"
                                       href="{{ urldecode(remove_query_params(["filter[{$attribute}]"])) }}">
                                        {{ ucwords(str_replace('.', ' ', $attribute)) }} = <code>{{ $filter }}</code>
                                    </a>
                                @endif
                            @endforeacH
                        </div>
                    </div>
                </div>
            @endif
            <div>
                <small class="text-uppercase">
                    <a href="?filter[state]=new"
                       class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'new' ? 'active' : '' }}">
                        <i class="fa fa-asterisk text-info"></i> New ({{ $newCount }})</a>
                    <a href="?filter[state]=assigned"
                    class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'assigned' ? 'active' : '' }}">
                        <i class="fa fa-hourglass-half text-accent"></i> Assigned ({{ $assignedCount }})</a>
                    <a href="?filter[state]=resolved"
                       class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'resolved' ? 'active' : '' }}">
                        <i class="fa fa-check-circle text-success"></i> Resolved ({{ $resolvedCount }})</a>

                </small>
            </div>
        </div>
        <hr>
        <form>
            <div class="row">
                <form action="">
                    <input type="hidden" name="search-query"
                           value="{{ urldecode(http_build_query(request()->query())) }}">
                    <div class="col-md-3">
                        <select name="search-filter" id="" class="form-control">
                            <option value="">Select a filter</option>
                            <option value="type.slug" required>Type</option>
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

        <hr>

        @unless($tickets->count())
            <p class="text-muted">No tickets match the provided criteria.</p>
        @else

            <div class="panel panel-filled">
                <div class="table-responsive">
                    <table class="table table-hover basic-datatable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Caller</th>
                            <th>State</th>
                            <th>Owned By</th>
                            <th>Updated At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($tickets->get() as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td class="text-info">{{ $ticket->type->name }}</td>
                                <td>
                                    <a href="?filter[caller.name]={{ $ticket->caller->name }}">{{ $ticket->caller->name }}</a>
                                </td>
                                <td>
                                    <a title="Show only {{ $ticket->state }} tickets"
                                       href="{{ "?filter[state]={$ticket->state}" }}"
                                       class="text-{{ $ticket->stateColor }} text-uppercase">{{ $ticket->state }}</a>
                                </td>
                                <td>
                                    @if($ticket->owner)
                                        <a href="?filter[owner.name]={{ $ticket->owner->name }}">{{ $ticket->owner->name }}</a>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


        @endunless
    </div>
@stop
