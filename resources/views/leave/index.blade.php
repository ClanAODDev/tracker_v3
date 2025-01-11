@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            <span class="hidden-xs">Leaves of Absence</span>
            <span class="visible-xs">Leave</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('leaves-of-absence', $division) !!}

        @if ($expiredLeave)
            <div class="alert alert-warning">
                You have
                <strong>expired leaves of absence</strong>! You should reach out to the member in case an extension is warranted, or remove the expired LOA. Expired leave is marked in red.
            </div>
        @endif
        <p>Leaves of absence are reserved for members who need to take extended leave for extenuating circumstances. It should not be something that is used on the whim. Division leadership should ensure that members are not abusing LOAs.</p>

        <h4 class="m-t-xl">Active Leaves of Absence</h4>

        @if ($membersWithLeave->count())
            @include ('leave.partials.leave-table')
        @else
            <p class="text-muted">No active leaves of absence</p>
        @endif

        <hr class="m-t-xl" />

        @include('application.partials.errors')


    </div>
@endsection
