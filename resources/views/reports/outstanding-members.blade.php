@extends('application.base-tracker')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Statistics
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-graph2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Outstanding Inactive Members
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row m-b-lg">
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ number_format($totals->population) }}</h1>
                        <div class="text-muted">Total Members</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-danger">{{ $totals->outstanding }}</span>
                        </h1>
                        <div class="text-muted">&gt; {{ $clanMax }} Days</div>
                        <small class="slight">{{ $totals->pctOutstanding }}% of clan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-warning">{{ $totals->inactive }}</span>
                        </h1>
                        <div class="text-muted">&gt; Division Max</div>
                        <small class="slight">{{ $totals->pctInactive }}% of clan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-success">{{ $totals->population - $totals->inactive }}</span>
                        </h1>
                        <div class="text-muted">Active</div>
                        <small class="slight">{{ $totals->population > 0 ? round(($totals->population - $totals->inactive) / $totals->population * 100, 1) : 0 }}% of clan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-b-lg">
            <div class="col-md-12">
                <p>
                    <strong>Outstanding</strong> members exceed the clan maximum of <code>{{ $clanMax }} days</code>.
                    <strong>Inactive</strong> members exceed their division's configured threshold (typically 30-45 days).
                    Members on leave are excluded from these counts.
                </p>
            </div>
        </div>

        <div class="panel table-responsive">
            <div class="panel-heading">
                Inactivity by Division
                <span class="text-muted pull-right">{{ now()->format('M j, Y') }}</span>
            </div>

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th class="col-xs-3">Division</th>
                    <th class="text-center col-xs-1">Pop</th>
                    <th class="text-center col-xs-2">&gt; {{ $clanMax }}d (Outstanding)</th>
                    <th class="text-center col-xs-2">&gt; Div Max (Inactive)</th>
                    <th class="text-center col-xs-3">Health</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($divisions as $division)
                    <tr>
                        <td>
                            {{ $division->name }}
                            <a href="{{ route('division', $division) }}" class="btn btn-default btn-xs pull-right">
                                <i class="fa fa-search"></i>
                            </a>
                        </td>
                        <td class="text-center text-muted">{{ $division->members_count }}</td>
                        <td class="text-center" data-order="{{ $division->outstandingCount }}">
                            @if($division->outstandingCount > 0)
                                <span class="text-danger">{{ $division->outstandingCount }}</span>
                                <small class="text-muted">({{ $division->pctOutstanding }}%)</small>
                            @else
                                <span class="text-success">0</span>
                            @endif
                        </td>
                        <td class="text-center" data-order="{{ $division->inactiveCount }}">
                            {{ $division->inactiveCount }}
                            <small class="text-muted">({{ $division->pctInactive }}%)</small>
                            <a href="{{ route('division.inactive-members', $division) }}" class="btn btn-default btn-xs pull-right" title="View inactive members">
                                <i class="fa fa-list"></i>
                            </a>
                        </td>
                        <td data-order="{{ 100 - $division->pctInactive }}">
                            <div class="progress" style="margin-bottom: 0; background-color: #404652;">
                                <div class="progress-bar progress-bar-success" style="width: {{ 100 - $division->pctInactive }}%"></div>
                                <div class="progress-bar progress-bar-warning" style="width: {{ $division->pctInactive - $division->pctOutstanding }}%"></div>
                                <div class="progress-bar progress-bar-danger" style="width: {{ $division->pctOutstanding }}%"></div>
                            </div>
                            <small class="text-muted">{{ $division->divisionMax }}d threshold</small>
                        </td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                <tr class="active">
                    <td><strong>Clan Total</strong></td>
                    <td class="text-center"><strong>{{ number_format($totals->population) }}</strong></td>
                    <td class="text-center">
                        <strong class="text-danger">{{ $totals->outstanding }}</strong>
                        <span class="text-muted">({{ $totals->pctOutstanding }}%)</span>
                    </td>
                    <td class="text-center">
                        <strong>{{ $totals->inactive }}</strong>
                        <span class="text-muted">({{ $totals->pctInactive }}%)</span>
                    </td>
                    <td>
                        <div class="progress" style="margin-bottom: 0; background-color: #404652;">
                            <div class="progress-bar progress-bar-success" style="width: {{ 100 - $totals->pctInactive }}%"></div>
                            <div class="progress-bar progress-bar-warning" style="width: {{ $totals->pctInactive - $totals->pctOutstanding }}%"></div>
                            <div class="progress-bar progress-bar-danger" style="width: {{ $totals->pctOutstanding }}%"></div>
                        </div>
                    </td>
                </tr>
                </tfoot>
            </table>

            <div class="panel-footer text-muted">
                <span class="text-success">&#9632;</span> Active
                <span class="text-warning" style="margin-left: 10px;">&#9632;</span> Inactive (&gt; Division Max)
                <span class="text-danger" style="margin-left: 10px;">&#9632;</span> Outstanding (&gt; {{ $clanMax }}d)
            </div>
        </div>

    </div>

@endsection