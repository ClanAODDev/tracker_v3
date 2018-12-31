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

                        <span class="badge"
                              title="Sergeants to Members Ratio">{{ ratio($division->sergeants_count, $division->members_count) }}</span>
                    </h4>

                    <div class="panel panel-filled pt-0">
                        <table class="table table-hover basic-datatable">
                            <thead>
                            <tr>
                                <th>Member</th>
                                <th>Position</th>
                                <th>Last Promoted</th>
                                <th class="hidden-sm hidden-xs">Last Trained</th>
                                <th class="hidden-sm hidden-xs">Trained By</th>
                                <th class="hidden-sm hidden-xs">XO Since</th>
                                <th class="hidden-sm hidden-xs">CO Since</th>
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
                                    <td>{{ $member->last_promoted_at ?? '--' }}</td>
                                    <td>{{ $member->last_trained_at }}</td>
                                    <td>{{ $member->last_trained_by ? $member->last_trained_by->name : '' }}</td>
                                    <td>{{ $member->xo_at }}</td>
                                    <td>{{ $member->co_at }}</td>
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
                                    <td>{{ $member->last_promoted_at ?? '--' }}</td>
                                    <td>{{ $member->last_trained_at }}</td>
                                    <td>{{ $member->last_trained_by ? $member->last_trained_by->name : '' }}</td>
                                    <td>{{ $member->xo_at }}</td>
                                    <td>{{ $member->co_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{--@foreach ( $division->sergeants as $member)--}}

                    {{--<a href="{{ route('member', $member->getUrlParams()) }}"--}}
                    {{--class="col-lg-3 panel panel-filled panel-c-accent m-r m-b">--}}
                    {{--<span class="panel-body">--}}
                    {{--{!! $member->present()->rankName !!}--}}
                    {{--<br />--}}
                    {{--<span class="slight text-muted text-uppercase">--}}
                    {{--{{ $member->position->name }}--}}
                    {{--</span>--}}
                    {{--</span>--}}
                    {{--</a>--}}
                    {{--@endforeach--}}
                </div>
            </div>
        @endforeach

    </div>
@stop

