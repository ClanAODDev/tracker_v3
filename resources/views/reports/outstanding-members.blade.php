@extends('application.base-tracker')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin CP
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Outstanding Inactive Members
        @endslot
    @endcomponent

    <div class="container-fluid">

        <p>Outstanding inactive members are those whose last TeamSpeak activity exceeds the clan maximum, currently
            <code>{{ config('app.aod.maximum_days_inactive') }} days</code>. Additionally, divisions can define a specific number of days before a member is listed as inactive, typically between 30-45 days.
        </p>

        <p>Percent inactive represents the percentage of the division that consists of inactive members.</p>

        <table class="table table-striped basic-datatable">
            <thead>
            <tr>
                <th>Division</th>
                <th>&gt; {{ config('app.aod.maximum_days_inactive') }} days</th>
                <th>&gt; Division Max</th>
                <th>Percent Inactive</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($divisions as $division)
                <tr>
                    <td>
                        {{ $division->name }}
                        <a href="{{ route('division', $division) }}" class="pull-right btn btn-default btn-xs">
                            <i class="fa fa-search"></i>
                        </a>
                    </td>
                    <td>{{ $division->outstanding_members }}</td>
                    <td>{{ $division->inactive_members }}</td>
                    <td>
                        {{ $division->percent_inactive }}%
                        <span class="census-pie"
                              data-colors="{{ json_encode(['#404652', '#5fbb60']) }}"
                              data-counts="{{ json_encode([$division->members_count-$division->inactive_members, $division->inactive_members]) }}">
                    </span>
                        <a href="{{ route('division.inactive-members', $division) }}" class="btn btn-default pull-right btn-xs">
                            <i class="fa fa-search"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

@endsection