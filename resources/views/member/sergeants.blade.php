@extends('application.base')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">

        @foreach ($divisions as $division)

            <div class="row m-b-xl">
                <div class="col-md-12">
                    <h4>
                        <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-medium" />
                        {{ $division->name }}
                        <span class="badge">{{ $division->sergeants_count }} Sergeants</span>
                        <span class="badge">{{ $division->members_count }} Members</span>
                        <span class="badge">{{ ratio($division->sergeants_count, $division->members_count) }}</span>

                    </h4>

                    <div class="panel panel-filled pt-0">
                        <table class="table table-hover basic-datatable">
                            <thead>
                            <tr>
                                <th>Member</th>
                                <th>Position</th>
                                <th>Last Promoted</th>
                                <th>Last Trained</th>
                                <th>Trained By</th>
                                <th>XO Since</th>
                                <th>CO Since</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $division->sergeants as $member)
                                <tr>
                                    <td>
                                        <a href="{{ route('member', $member->getUrlParams()) }}">
                                            {!! $member->present()->rankName !!}
                                        </a>
                                    </td>
                                    <td class="slight text-uppercase">{{ $member->position->name }}</td>
                                    <td>{{ $member->last_promoted_at ? $member->last_promoted_at->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $member->xo_at ? $member->xo_at->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $member->co_at ? $member->co_at->format('Y-m-d') : '--' }}</td>
                                </tr>
                            @endforeach

                            @foreach($division->staffSergeants as $member)

                                <tr>
                                    <td>
                                        <a href="{{ route('member', $member->getUrlParams()) }}">
                                            {!! $member->present()->rankName !!}
                                        </a>
                                    </td>
                                    <td class="slight text-uppercase" style="color: cyan">
                                        Assigned Staff Sergeant
                                    </td>
                                    <td>{{ $member->last_promoted_at ? $member->last_promoted_at->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $member->xo_at ? $member->xo_at->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $member->co_at ? $member->co_at->format('Y-m-d') : '--' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@stop

