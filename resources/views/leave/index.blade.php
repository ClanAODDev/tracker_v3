@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            {{ $division->description }}
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

        <hr class="m-t-xl m-b-xl" />

        @include('application.partials.errors')
        <div class="panel panel-filled panel-c-accent">
            <div class="panel-heading">
                Create Request
            </div>
            <div class="panel-body">
                <form action="{{ route('leave.store', $division->abbreviation) }}" method="post">
                    {!! Form::model(App\Note::class, ['method' => 'post', 'route' => ['leave.store', $division->abbreviation]]) !!}
                    @include('leave.forms.create-leave')
                    {!! Form::close() !!}
                </form>
            </div>
        </div>
    </div>
@stop
