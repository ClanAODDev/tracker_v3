@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->abbreviation) }}">
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

    <div class="container-fluid">

        {!! Form::model($platoon, ['method' => 'patch', 'route' => ['updatePlatoon', $division->abbreviation, $platoon]]) !!}
        @include('platoon.forms.edit-platoon-form')
        {!! Form::close() !!}

        @can('delete', $platoon)
            <hr />

            {!! Form::model($platoon, ['method' => 'delete', 'route' => ['updatePlatoon', $division->abbreviation, $platoon]]) !!}
            @include('platoon.forms.delete-platoon-form')
            {!! Form::close() !!}

            <hr />

        @endcan

        @include ('platoon.partials.feed')
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

@stop


