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

    <form id="create-platoon" method="post"
          action="{{ route('savePlatoon', $division->slug) }}">
        @csrf

        <div class="container-fluid">
            @include('platoon.forms.create-platoon-form')
        </div>

    </form>

@endsection


