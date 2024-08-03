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

        @include('squad.forms.edit-squad-form')

        @can('delete', $squad)
            <hr/>
            @include('squad.forms.delete-squad-form')
        @endcan

        @if(count($squad->activity))
            <hr/>
            @include ('squad.partials.feed')
        @endif

    </div>

@endsection


