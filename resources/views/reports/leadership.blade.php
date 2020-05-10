@extends('application.base')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row m-b-xl">

            <div class="col-md-9">

                <div class="panel panel-c-accent panel-filled">
                    <div class="panel-heading">
                        <span class="text-muted">Filter View</span>
                    </div>
                    <div class="panel-body">
                        <input id="showSsgts" name="showSsgts" type="checkbox">
                        <label for="showSsgts">Show Staff Sergeants</label>
                    </div>
                </div>


                <h4 id="leadership"><img src="{{ asset('images/aod-logo.png') }}" class="division-icon-medium"/> Clan
                    Leadership </h4>
                <div class="panel">
                    <table class="table table-hover table-striped basic-datatable">
                        <thead>
                        <tr>
                            <th>Member</th>
                            <th>Last Promoted</th>
                            <th>Last Trained</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($leadership as $member)
                            <tr>
                                <td>
                                    <a href="{{ route('member', $member->getUrlParams()) }}" class="rank-hover">
                                        {!! $member->present()->rankName !!}
                                    </a>
                                </td>
                                <td>{{ $member->last_promoted_at ? $member->last_promoted_at->format('Y-m-d') : '--' }}</td>
                                <td>{{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>


                @foreach ($divisions as $division)
                    <h4 id="{{ $division->abbreviation }}">
                        <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-medium"/>
                        {{ $division->name }}
                        <span class="badge" title="Sgts and SSgts">{{ $division->sgt_and_ssgt_count }} Sgts*</span>
                        <span class="badge">{{ $division->members_count }} Members</span>
                        <span class="badge">{{ ratio($division->sgt_and_ssgt_count, $division->members_count) }}*</span>
                    </h4>

                    <div class="panel pt-0">
                        <table class="table table-striped table-hover basic-datatable">
                            <thead>
                            <tr>
                                <th>Member</th>
                                <th>Position</th>
                                <th>Last Promoted</th>
                                <th class="hidden-xs hidden-sm">Last Trained</th>
                                <th class="hidden-xs hidden-sm">XO Since</th>
                                <th class="hidden-xs hidden-sm">CO Since</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $division->sergeants as $member)
                                <tr>
                                    <td>
                                        <a href="{{ route('member', $member->getUrlParams()) }}" class="rank-hover">
                                            {!! $member->present()->rankName !!}
                                        </a>
                                    </td>
                                    <td class="slight text-uppercase">{{ $member->position->name }}</td>
                                    <td>{{ $member->last_promoted_at ? $member->last_promoted_at->format('Y-m-d') : '--' }}</td>
                                    <td class="hidden-xs hidden-sm">{{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }}</td>
                                    <td class="hidden-xs hidden-sm">{{ $member->xo_at ? $member->xo_at->format('Y-m-d') : '--' }}</td>
                                    <td class="hidden-xs hidden-sm">{{ $member->co_at ? $member->co_at->format('Y-m-d') : '--' }}</td>
                                </tr>
                            @endforeach

                            @foreach($division->staffSergeants as $member)

                                <tr data-ssgt="1">
                                    <td>
                                        <a href="{{ route('member', $member->getUrlParams()) }}" class="rank-hover"
                                        >
                                            {!! $member->present()->rankName !!}
                                        </a>
                                    </td>
                                    <td class="slight text-uppercase" style="color: cyan;">
                                        Assigned Staff Sergeant
                                    </td>
                                    <td>{{ $member->last_promoted_at ? $member->last_promoted_at->format('Y-m-d') : '--' }}</td>
                                    <td class="hidden-xs hidden-sm">{{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }}</td>
                                    <td class="hidden-xs hidden-sm">{{ $member->xo_at ? $member->xo_at->format('Y-m-d') : '--' }}</td>
                                    <td class="hidden-xs hidden-sm">{{ $member->co_at ? $member->co_at->format('Y-m-d') : '--' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="panel-footer text-right">
                            <small class="slight text-muted">*Count, ratio consists of SGT and SSGT only. Assigned SSGT
                                not included</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-md-3 hidden-xs hidden-sm pull-right" style="position: sticky; top: 75px">
                <div class="panel panel-filled panel-c-accent">
                    <div class="panel-heading"><strong>Navigation</strong></div>
                    <ul class="page-nav">
                        <li><a href="#leadership" class="smooth-scroll">Clan Leadership</a></li>
                        @foreach($divisions as $division)
                            <li>
                                <a href="#{{ $division->abbreviation }}" class="smooth-scroll">{{ $division->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>

    </div>


    <script>
        $(document).ready(function () {
            $('#showSsgts').click(function () {
                if ($(this).prop("checked") == true) {
                    $('tr').not('[data-ssgt]').hide();
                } else if ($(this).prop("checked") == false) {
                    $('tr').not('[data-ssgt]').show();
                }
            });
        });

    </script>
@stop


