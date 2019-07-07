@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                     class="division-icon-large" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {{ $squad->name }}
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Form::model($squad, ['method' => 'patch', 'route' => ['updateSquad', $division->abbreviation, $platoon, $squad]]) !!}
        @include('squad.forms.edit-squad-form')
        {!! Form::close() !!}

        @can('delete', $squad)
            <hr />
            {!! Form::model($squad, ['method' => 'delete', 'route' => ['deleteSquad', $division->abbreviation, $platoon, $squad]]) !!}
            @include('squad.forms.delete-squad-form')
            {!! Form::close() !!}
        @endcan

        @if(count($squad->activity))
            <hr />
            @include ('squad.partials.feed')
        @endif

    </div>

@stop


