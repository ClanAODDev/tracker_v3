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

    <form id="create-platoon" method="post"
          action="{{ route('savePlatoon', $division->abbreviation) }}">

        <div class="container-fluid">
            @component('division.forms.createPlatoonOrSquad', ['division' => $division])
                @slot('helpText')
                    <h4>Create a {{ $division->locality('platoon') }}</h4>
                    <p>When assigning a leader, the tracker will automatically set their position to {{ $division->locality('platoon leader') }}.</p>

                    <p>Leaders can only be assigned to a single {{ $division->locality('platoon') }} and they must belong to the current division.</p>
                @endslot
                @slot('type')
                    {{ $division->locality('platoon') }}
                @endslot
                @slot('leader')
                    {{ $division->locality('platoon leader') }}
                @endslot
            @endcomponent
        </div>

    </form>

@stop


