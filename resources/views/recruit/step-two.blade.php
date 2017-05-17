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
            Recruit New Member
        @endslot
    @endcomponent

    <div class="container-fluid">
        <h3>Select Recruit Assignment</h3>
        <p>Depending on your division configuration, members must be assigned to a {{ $division->locality('platoon') }} and {{ $division->locality('squad') }}. For convenience, your current assignment has been preselected. Changing the {{ $division->locality('platoon') }} will automatically update the list of {{ str_plural($division->locality('squad')) }} available.</p>

        <hr />

        <h4>{{ str_plural($division->locality('platoon')) }}</h4>

        <form action="{{ route('stepOne', [$division->abbreviation]) }}" method="post">

            {{ csrf_field() }}

            <div class="btn-group form-group" data-toggle="buttons">
                @foreach ($division->platoons as $platoon)
                    <label class="btn btn-accent">
                        {{ $platoon->name }}
                        <input type="radio" name="platoon" value="{{ $platoon->id }}" autocomplete="off" />
                    </label>
                @endforeach
            </div>
            <button type="submit" class="form-control">Submit</button>
        </form>
    </div>
@stop