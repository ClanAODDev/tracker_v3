@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
            @include('division.partials.edit-division-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            Member Notes
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="panel">
            <div class="panel-body">
                @foreach ($tags as $tag)
                    <a class="btn btn-default"
                       href="{{ route('division.notes', [$division->abbreviation, $tag]) }}">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-12">
                @forelse ($notes as $note)
                    <div class="panel panel-filled note {{ $note->type }}">
                        <div class="panel-body">
                            {{ $note->body }}
                        </div>
                    </div>
                @empty
                    <h4>Oops...</h4>
                    <p>No notes match that criteria</p>
                @endforelse

            </div>
        </div>

    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/division.js?v=1.3') !!}"></script>
@stop
