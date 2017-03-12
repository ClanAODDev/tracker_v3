@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        @endslot
        @slot ('heading')
            {{ $division->name }} Division
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <form id="create-squad" method="post"
          action="{{ route('saveSquad', [$division->abbreviation, $platoon->id]) }}">

        <div class="container-fluid">
            @component('division.forms.createPlatoonOrSquad', ['division' => $division])
                @slot('helpText')
                    <h4>Create a {{ $division->locality('squad') }}</h4>
                    <p>When assigning a leader, the tracker will automatically set their position to {{ $division->locality('squad leader') }}.</p>

                    <p>Leaders can only be assigned to a single {{ $division->locality('squad') }} and they must belong to the current division.</p>
                @endslot
                @slot('type')
                    {{ $division->locality('squad') }}
                @endslot
                @slot('leader')
                    {{ $division->locality('squad leader') }}
                @endslot
            @endcomponent
        </div>

    </form>

@stop


