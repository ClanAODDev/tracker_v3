@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $division->name }} Division
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        <form id="create-squad" method="post" class="margin-top-20"
              action="{{ route('storeSquad', [$division->slug, $platoon->id]) }}">
            @include('squad.forms.create-squad-form', ['actionText' => 'Create New'])
            @csrf
        </form>
    </div>

@endsection


