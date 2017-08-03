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

        <div class="row">
            <div class="col-xs-12 m-b-xl">
                @foreach ($divisions as $division)
                    <a href="#{{ $division->abbreviation }}"
                       class="btn btn-default m-b m-r smooth-scroll">{{ $division->name }}</a>
                @endforeach
            </div>
        </div>

        @foreach ($divisions as $division)
            <div class="panel panel-filled m-b-xl" id="{{ $division->abbreviation }}">
                <div class="panel-heading">
                    {{ $division->name }} Division
                </div>
                <div class="panel-body">
                    <table class="table adv-datatable table-hover">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Rank</th>
                            <th class="hidden-sm hidden-xs">Join date</th>
                            <th>Last forum activity</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($division->members as $member)
                            <tr style="cursor:pointer;"
                                onclick="window.location.href = '{{ route('member', $member->clan_id) }}';">
                                <td>
                                    {{ $member->name }}
                                    <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                                </td>
                                <td>{{ $member->rank_id }}</td>
                                <td class="hidden-sm hidden-xs">{{ $member->join_date }}</td>
                                <td>{{ $member->last_activity->format('Y-m-d') }}</td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

@stop
