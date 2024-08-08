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

        <form action="{{ route('updatePlatoon', [$division->slug, $platoon]) }}" method="post">
            @csrf
            @method('put')
            @include('platoon.forms.edit-platoon-form')
        </form>

        @can('delete', $platoon)
            <hr/>
            <form action="{{ route('updatePlatoon', [$division->slug, $platoon]) }}" method="post">
                @csrf
                @method('delete')
                @include('platoon.forms.delete-platoon-form')
            </form>

        @endcan

        @if(count($platoon->activity))
            <hr/>
            @include ('platoon.partials.feed')
        @endif
    </div>


    <script>
        // omit leader field if using TBA
        $('#is_tba').click(function () {
            if ($('#is_tba').is(':checked')) {
                $('#leader').prop('disabled', true);
            } else {
                $('#leader').prop('disabled', false);
            }
        });
    </script>

@endsection


