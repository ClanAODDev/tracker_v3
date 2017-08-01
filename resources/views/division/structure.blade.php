@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Division Structure</span>
            <span class="visible-xs">Structure</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('division-structure', $division) !!}

        <div class="row">
            <div class="col-md-12">
                <h4>Generated Structure</h4>
                <div class="form-group">
                    <textarea name="structure" id="structure" class="form-control"
                              rows="10" style="resize: vertical;">{{ $data }}</textarea>
                </div>
                <span class="text-muted pull-right">{{ strlen($data) }} characters</span>

                @can ('manageDivisionStructure', auth()->user())
                    <a class="btn btn-default"
                       href="{{ route('division.edit-structure', $division->abbreviation) }}">
                        <i class="fa fa-wrench text-accent"></i> Go to editor</a>
                @endcan

                <button data-clipboard-target="#structure" class="copy-to-clipboard btn-success btn"><i
                            class="fa fa-clone"></i> Copy Contents
                </button>
            </div>
        </div>
    </div>
@stop