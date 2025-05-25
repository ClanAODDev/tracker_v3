@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $platoon->name }}
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

        <h4>Manage {{ $division->locality('Squad') }} Assignments</h4>
        <p>Drag members between {{ Str::plural($division->locality('squad')) }} to assign them. Only {{ $division->locality('Squad') }} members will be shown; squad leaders cannot be reassigned from this view.</p>
        <div class="m-t-xl">
            <a href="{{ route('platoon', [$division->slug, $platoon]) }}" class="btn btn-default">Cancel</a>
            <a class="btn btn-success"
               href="{{ route('createSquad', [$division->slug, $platoon]) }}">
                <i class="fa fa-plus"></i> Create {{ $division->locality('squad') }}
            </a>
        </div>

        <hr />

        <p class="alert alert-warning"><i
                    class="fa fa-exclamation-circle"></i> You can rearrange {{ Str::plural($division->locality('squad')) }} to more easily move members between them. To do so, click and drag the {{ $division->locality('squad') }} name.
        </p>

        <div class="panel panel-filled panel-c-warning">
            <div class="panel-heading">
                <i class="fas fa-trash text-warning"></i> Unassign from {{ $division->locality('platoon') }} and {{ $division->locality('squad') }}
            </div>
            <div class="panel-body">
                <div class="mod-plt sortable-squad">
                    <div class="col-md-3">
                        <ul class="sortable" data-squad-id="0"
                            style="border: thin dashed rgba(255,255,255,.3); padding: 15px; border-radius: 5px;"></ul>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <p>Drag members here to
                    <em class="text-warning">immediately unassign</em> them from their current {{ $division->locality('platoon') }} and {{ $division->locality('squad') }}.
                </p>
            </div>
        </div>

        <div class="m-t-xl">

            <div class="row mod-plt sortable-squad">

                @if (count($platoon->unassigned))
                    <div class="col-md-3">
                        <h5 class="grabbable"><i class="fa fa-drag-handle text-muted"></i>
                            <strong class="text-danger text-uppercase">Unassigned</strong>
                            <span class="pull-right badge badge-default count">{{ count($platoon->unassigned) }}</span>
                        </h5>
                        <hr />

                        <ul class="sortable">
                            @foreach ($platoon->unassigned as $member)
                                <li class="list-group-item grabbable" data-member-id="{{ $member->id }}"><i
                                            class="fa fa-drag-handle text-muted pull-right"></i><span
                                            class="no-select">{{ $member->present()->rankName }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @foreach ($platoon->squads as $squad)
                    @include('platoon.partials.droppable-member')
                @endforeach
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@endsection
