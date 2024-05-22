@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $squad->name }}
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Form::model($squad, ['method' => 'patch', 'route' => ['updateSquad', $division->slug, $platoon, $squad]]) !!}
        @include('squad.forms.edit-squad-form')
        {!! Form::close() !!}

        @can('delete', $squad)
            <hr />
            {!! Form::model($squad, ['method' => 'delete', 'route' => ['deleteSquad', $division->slug, $platoon, $squad]]) !!}
            @include('squad.forms.delete-squad-form')
            {!! Form::close() !!}
        @endcan

        @if(count($squad->activity))
            <hr />
            @include ('squad.partials.feed')
        @endif

    </div>

@endsection


