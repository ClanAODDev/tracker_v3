@extends('application.base-tracker')

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

        <div class="row">
            <div class="panel">
                <div class="panel-body">
                    <a class="btn btn-default"
                       href="{{ route('division.notes', $division->abbreviation) }}?type=misc">
                        Misc
                    </a>

                    <a class="btn btn-default"
                           href="{{ route('division.notes', $division->abbreviation) }}?type=negative">
                        Negative
                    </a>

                    <a class="btn btn-default"
                       href="{{ route('division.notes', $division->abbreviation) }}?type=Positive">
                        Positive
                    </a>

                    <a href="{{ route('division.notes', $division->abbreviation) }}"
                       class="btn btn-default text-muted">Reset Filter</a>

                </div>
            </div>
        </div>

        <h3>{{ $filter->name ?? "All Notes" }} ({{ count($notes) }})</h3>
        <hr />

        <div class="row">
            <div class="col-md-12">
                @forelse ($notes as $note)
                    <a href="{{ route('member', $note->member->getUrlParams()) }}"
                       class="panel panel-filled note {{ $note->type }}">
                        <div class="panel-heading">
                            {{ $note->member->name }} - {{ $note->updated_at->format('M d, Y')}}
                        </div>
                        <div class="panel-body">
                            {{ $note->body }} <span class="text-muted">- {{ ($note->author) ? $note->author->name : 'Unk' }}</span>
                        </div>
                    </a>
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
