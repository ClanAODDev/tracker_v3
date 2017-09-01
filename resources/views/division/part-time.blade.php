@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
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

        @if (count($members))
            <table class="table adv-datatable table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Ingame Name</th>
                    <th class="no-sort col-xs-1"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($members as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                            {{ $member->name }}
                            <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                        </td>
                        <td>
                            @forelse ($member->handles as $handle)
                                <code>{{ $handle->pivot->value }}</code>
                                @if ($handle->url)
                                    <a href="{{ $handle->url }}{{ $handle->pivot->value }}" target="_blank">
                                        <i class="fa fa-external-link"></i>
                                    </a>
                                @endif
                            @empty
                                <span class="text-muted slight">NONE FOR DIVISION</span>
                            @endforelse
                        </td>
                        <td class="text-center">
                            @can ('create', App\Member::class)
                                <a class="btn btn-danger"
                                   href="{{ route('removePartTimer', [$division->abbreviation, $member->clan_id]) }}">
                                    <i class="fa fa-trash text-danger"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <h4><i class="fa fa-times-circle-o text-danger"></i> No Part-Time Members</h4>
            <p>This division currently has no part-time members assigned. To assign a member, use the search bar at the top of the page to find their profile, edit their member information, and select your division in the part-time tab.</p>
        @endif

    </div>
@stop
