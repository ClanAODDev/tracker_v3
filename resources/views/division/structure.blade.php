@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <textarea name="structure" id="structure" class="form-control"
                              rows="10" style="resize: vertical;">{{ $data }}</textarea>
                </div>

                @can ('manageDivisionStructure')
                    <a class="btn btn-default"
                       href="{{ route('division.edit-structure', $division->abbreviation) }}">Back to editor</a>
                @endcan

                <button data-clipboard-target="#structure" class="copy-to-clipboard btn-success btn">Copy Contents
                </button>
            </div>
        </div>
    </div>
@stop