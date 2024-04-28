@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
        @slot ('heading')
            Part-Timers
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('part-timers', $division) !!}

        @include('division.partials.select-panel')

        @if (count($members))
            <div class='panel-body border-bottom'>
                <div id='playerFilter'></div>
            </div>
            <table class="table table-hover basic-datatable for-pm-selection">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Ingame Name</th>
                    <th>Status</th>
                    <th class="no-sort col-xs-1"></th>
                    <th class="col-hidden">Clan Id</th>

                </tr>
                </thead>
                <tbody>
                @foreach ($members as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->getUrlParams()) }}"><i
                                    class="fa fa-search"></i></a>
                            {{ $member->name }}
                            <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                        </td>
                        <td>
                            @if ($member->handle)
                                <code>{{ $member->handle->pivot->value }}</code>
                                @if ($member->handle->url)
                                    <a href="{{ $member->handle->url }}{{ $member->handle->pivot->value }}"
                                       target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            @else
                                <span class="text-muted slight">NONE FOR DIVISION</span>
                            @endif
                        </td>
                        <td>
                            <small>
                                @if ($member->division_id === 0)
                                    <i class="fa fa-times text-danger"></i> REMOVED FROM AOD
                                @else
                                    <span class="text-muted">ACTIVE</span>
                                @endif
                            </small>
                        </td>
                        <td class="text-center">
                            @can ('create', App\Models\Member::class)
                                <a class="btn btn-danger"
                                   href="{{ route('removePartTimer', [$division->slug, $member->clan_id]) }}">
                                    <i class="fa fa-trash text-danger"></i>
                                </a>
                            @endcan
                        </td>
                        <td class="col-hidden">{{ $member->clan_id }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <h4><i class="fa fa-times-circle-o text-danger"></i> No Part-Time Members</h4>
            <p>This division currently has no part-time members assigned. To assign a member, use the search bar
                at the top of the page to find their profile, edit their member information, and select your division
                in the part-time tab.</p>

    @endif

@endsection


