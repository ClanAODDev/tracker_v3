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

        <div class="row">
            <div class="panel">
                <div class="panel-body">
                    @forelse ($tags as $tag)
                        <a class="btn btn-default"
                           href="{{ route('division.notes', [$division->abbreviation, $tag->slug]) }}">
                            {{ $tag->name }}
                        </a>
                    @endforelse
                    <a href="{{ route('division.notes', $division->abbreviation) }}"
                       class="btn btn-default text-muted">Reset Filter</a>
                </div>
            </div>
        </div>

        <h3>{{ $filter->name or "All Notes" }} ({{ count($notes) }})</h3>
        <hr />

        <div class="row">
            <div class="col-md-12">
                @forelse ($notes as $note)
                    <a href="{{ route('member', $note->member->getUrlParams()) }}" class="panel panel-filled note {{ $note->type }}">
                        <div class="panel-heading">
                            {{ $note->member->name }} - {{ $note->updated_at->format('M d, Y')}}
                            <div class="pull-right">
                                @forelse ($note->tags as $tag)
                                    <div class="label label-default">{{ $tag->name }}</div>
                                @empty
                                    <div class="label label-default text-muted">No tags</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="panel-body">
                            {{ $note->body }}
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
