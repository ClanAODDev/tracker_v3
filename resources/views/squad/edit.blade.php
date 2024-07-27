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

        <form action="{{ route('updateSquad', [$division->slug, $platoon, $squad]) }}" method="post">
            @include('squad.forms.edit-squad-form')
            @csrf
        </form>

        @can('delete', $squad)
            <hr/>
            <form action="{{ route('deleteSquad', [$division->slug, $platoon, $squad]) }}" method="post">
                @include('squad.forms.delete-squad-form')
            </form>
        @endcan

        @if(count($squad->activity))
            <hr/>
            @include ('squad.partials.feed')
        @endif

    </div>

@endsection


