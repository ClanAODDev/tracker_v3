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
            Clan statistics and demographic information
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row m-b-lg">
            <div class="col-md-12">
                <p>Division turnover consists of the count of new members in a 30/60/90 day window. Each column is inclusive, and is based on the current division population.</p>
            </div>
        </div>

        <div class="panel table-responsive">
            <div class="panel-heading">
                Division Churn - Percentage of new members in 30/60/90
            </div>

            <table class="table table-hover basic-datatable">

                <thead>
                <tr>
                    <th class="col-xs-4">Division</th>
                    <th class="text-center col-xs-2">30 Days</th>
                    <th class="text-center col-xs-2">60 Days</th>
                    <th class="text-center col-xs-2">90 Days</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($divisions as $division)
                    <tr>
                        <td>{{ $division->name }}</td>
                        <td class="text-center" title="{{ $division->new_members_last30_count }}, {{ $division->members_count }}">
                            <code>{{ percent(max($division->new_members_last30_count, 1), $division->members_count)  }}%</code>
                        </td>
                        <td class="text-center" title="{{ $division->new_members_last60_count }}, {{ $division->members_count }}">
                            <code>{{ percent(max($division->new_members_last60_count, 1), $division->members_count) }}%</code>
                        </td>
                        <td class="text-center" title="{{ $division->new_members_last90_count }}, {{ $division->members_count }}">
                            <code>{{ percent(max($division->new_members_last90_count, 1), $division->members_count) }}%</code>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="panel-footer text-muted">
                <p>Populations reflect data collected during last census.</p>
            </div>
        </div>

    </div>
@endsection

