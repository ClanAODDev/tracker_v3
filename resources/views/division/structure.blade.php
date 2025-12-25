@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
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

        <div class="row m-b-lg">
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ number_format($stats->members) }}</h1>
                        <div class="text-muted">Members</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $stats->platoons }}</h1>
                        <div class="text-muted">{{ $division->locality('platoon') }}s</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $stats->squads }}</h1>
                        <div class="text-muted">{{ $division->locality('squad') }}s</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $stats->leaders }}</h1>
                        <div class="text-muted">Leaders</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        <i class="fa fa-file-alt"></i> Generated Output
                        <span class="badge pull-right">{{ strlen($data) }} characters</span>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <textarea name="structure" id="structure" class="form-control structure-output"
                                  rows="15" readonly>{{ $data }}</textarea>
                    </div>
                    <div class="panel-footer">
                        <div class="structure-actions">
                            <div class="structure-actions-left">
                                <button data-clipboard-target="#structure" class="copy-to-clipboard btn-success btn">
                                    <i class="fa fa-clone"></i> Copy to Clipboard
                                </button>
                                @can('editDivisionStructure', auth()->user())
                                    <a class="btn btn-default" href="{{ route('division.edit-structure', $division->slug) }}">
                                        <i class="fa fa-wrench text-accent"></i> Edit Template
                                    </a>
                                @endcan
                            </div>
                            @if($lastUpdated)
                                <div class="structure-last-updated text-muted">
                                    <i class="fa fa-clock"></i>
                                    Template last updated {{ $lastUpdated->created_at->diffForHumans() }}
                                    @if($lastUpdated->user)
                                        by {{ $lastUpdated->user->name }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
