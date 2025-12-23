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
            Division Turnover Report
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row m-b-lg">
            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-info">{{ $totals->last30 }}</span>
                        </h1>
                        <div class="text-muted">New in 30 Days</div>
                        <small class="slight">{{ $totals->pct30 }}% of clan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-warning">{{ $totals->last60 }}</span>
                        </h1>
                        <div class="text-muted">New in 60 Days</div>
                        <small class="slight">{{ $totals->pct60 }}% of clan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-success">{{ $totals->last90 }}</span>
                        </h1>
                        <div class="text-muted">New in 90 Days</div>
                        <small class="slight">{{ $totals->pct90 }}% of clan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-b-lg">
            <div class="col-md-12">
                <p>Division turnover shows the count and percentage of new members recruited within 30/60/90 day windows. Each column is cumulative (90 days includes 60 and 30).</p>
            </div>
        </div>

        <div class="panel table-responsive">
            <div class="panel-heading">
                Division Turnover
                <span class="text-muted pull-right">{{ now()->format('M j, Y') }}</span>
            </div>

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th class="col-xs-3">Division</th>
                    <th class="text-center col-xs-1">Population</th>
                    <th class="text-center col-xs-2">30 Days</th>
                    <th class="text-center col-xs-2">60 Days</th>
                    <th class="text-center col-xs-2">90 Days</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($divisions as $division)
                    <tr>
                        <td>{{ $division->name }}</td>
                        <td class="text-center text-muted">{{ $division->members_count }}</td>
                        <td class="text-center" data-order="{{ $division->new_members_last30_count }}">
                            <div class="progress" style="margin-bottom: 0; min-width: 80px; background-color: #404652;">
                                <div class="progress-bar progress-bar-info" style="width: {{ min($division->pct30, 100) }}%"></div>
                            </div>
                            <small>{{ $division->new_members_last30_count }} <span class="text-muted">({{ $division->pct30 }}%)</span></small>
                        </td>
                        <td class="text-center" data-order="{{ $division->new_members_last60_count }}">
                            <div class="progress" style="margin-bottom: 0; min-width: 80px; background-color: #404652;">
                                <div class="progress-bar progress-bar-warning" style="width: {{ min($division->pct60, 100) }}%"></div>
                            </div>
                            <small>{{ $division->new_members_last60_count }} <span class="text-muted">({{ $division->pct60 }}%)</span></small>
                        </td>
                        <td class="text-center" data-order="{{ $division->new_members_last90_count }}">
                            <div class="progress" style="margin-bottom: 0; min-width: 80px; background-color: #404652;">
                                <div class="progress-bar progress-bar-success" style="width: {{ min($division->pct90, 100) }}%"></div>
                            </div>
                            <small>{{ $division->new_members_last90_count }} <span class="text-muted">({{ $division->pct90 }}%)</span></small>
                        </td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                <tr class="active">
                    <td><strong>Clan Total</strong></td>
                    <td class="text-center"><strong>{{ number_format($totals->population) }}</strong></td>
                    <td class="text-center">
                        <strong>{{ $totals->last30 }}</strong>
                        <span class="text-muted">({{ $totals->pct30 }}%)</span>
                    </td>
                    <td class="text-center">
                        <strong>{{ $totals->last60 }}</strong>
                        <span class="text-muted">({{ $totals->pct60 }}%)</span>
                    </td>
                    <td class="text-center">
                        <strong>{{ $totals->last90 }}</strong>
                        <span class="text-muted">({{ $totals->pct90 }}%)</span>
                    </td>
                </tr>
                </tfoot>
            </table>

            <div class="panel-footer text-muted">
                Data calculated in real-time based on member join dates.
            </div>
        </div>

    </div>
@endsection

