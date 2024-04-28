@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->slug) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            </a>
        @endslot
        @slot ('heading')
            {{ $division->name }} Division
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <form id="create-platoon" method="post"
          action="{{ route('savePlatoon', $division->slug) }}">

        <div class="container-fluid">
            @include('platoon.forms.edit-platoon-form')
        </div>

    </form>

@endsection


