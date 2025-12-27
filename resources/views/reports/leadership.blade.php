@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            Leadership Structure
        @endslot
        @slot ('icon')
            <img src="{{ getThemedLogoPath() }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Clan leadership and division sergeants
        @endslot
    @endcomponent

    <div class="container-fluid leadership-page">
        <div class="row">
            <div class="col-md-9">
                <div class="leadership-card leadership-card--primary" id="leadership">
                    <div class="leadership-header leadership-header--clan">
                        <h4>
                            <img src="{{ asset('images/aod-logo.png') }}" alt="AOD"/>
                            Clan Leadership
                        </h4>
                        <div class="leadership-stats">
                            <span class="badge">{{ $leadership->count() }} Members</span>
                        </div>
                    </div>
                    <div class="panel">
                        <table class="table leadership-table basic-datatable">
                            <thead>
                            <tr>
                                <th>Member</th>
                                <th class="hidden-xs">Last Promoted</th>
                                <th class="hidden-xs">Last Trained</th>
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
                                    <td class="hidden-xs">{{ $member->last_promoted_at?->format('Y-m-d') ?? '--' }}</td>
                                    <td class="hidden-xs">{{ $member->last_trained_at?->format('Y-m-d') ?? '--' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @foreach ($divisions as $division)
                    <div class="leadership-card" id="{{ $division->abbreviation }}">
                        <div class="leadership-header">
                            <h4>
                                <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}"/>
                                {{ $division->name }}
                            </h4>
                            <div class="leadership-stats">
                                <span class="badge" title="Sergeants and Staff Sergeants">{{ $division->sgt_and_ssgt_count }} Sgts</span>
                                <span class="badge">{{ $division->members_count }} Members</span>
                                <span class="badge" title="Sgt to Member Ratio">{{ ratio($division->sgt_and_ssgt_count, $division->members_count) }}</span>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="table-responsive">
                            <table class="table leadership-table leadership-table--division basic-datatable">
                                <thead>
                                <tr>
                                    <th>Member</th>
                                    <th class="hidden">Position Sort</th>
                                    <th>Position</th>
                                    <th class="hidden-xs">Last Promoted</th>
                                    <th class="hidden-xs hidden-sm">Last Trained</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($division->sergeants as $member)
                                    @php
                                        $rowClass = match($member->position) {
                                            \App\Enums\Position::COMMANDING_OFFICER => 'leadership-row--co',
                                            \App\Enums\Position::EXECUTIVE_OFFICER => 'leadership-row--xo',
                                            default => ''
                                        };
                                        $badgeClass = match($member->position) {
                                            \App\Enums\Position::COMMANDING_OFFICER => 'position-badge--co',
                                            \App\Enums\Position::EXECUTIVE_OFFICER => 'position-badge--xo',
                                            \App\Enums\Position::PLATOON_LEADER => 'position-badge--tl',
                                            \App\Enums\Position::SQUAD_LEADER => 'position-badge--tl',
                                            \App\Enums\Position::CLAN_ADMIN => 'position-badge--admin',
                                            default => 'position-badge--member'
                                        };
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>
                                            <a href="{{ route('member', $member->getUrlParams()) }}" class="rank-hover">
                                                {!! $member->present()->rankName !!}
                                            </a>
                                        </td>
                                        <td class="hidden">{{ $member->position === \App\Enums\Position::CLAN_ADMIN ? 0 : $member->position->value }}</td>
                                        <td>
                                            <span class="position-badge {{ $badgeClass }}">
                                                {{ $member->position->getAbbreviation() ?: 'SGT' }}
                                            </span>
                                        </td>
                                        <td class="hidden-xs">{{ $member->last_promoted_at?->format('Y-m-d') ?? '--' }}</td>
                                        <td class="hidden-xs hidden-sm">{{ $member->last_trained_at?->format('Y-m-d') ?? '--' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-md-3 hidden-xs hidden-sm">
                <div class="leadership-nav">
                    <div class="panel">
                        <div class="panel-heading">
                            <i class="fa fa-compass"></i> Navigation
                        </div>
                        <ul class="nav-list">
                            <li class="nav-item--clan">
                                <a href="#leadership" class="smooth-scroll">
                                    <img src="{{ asset('images/aod-logo.png') }}" alt="AOD"/>
                                    Clan Leadership
                                    <span class="badge">{{ $leadership->count() }}</span>
                                </a>
                            </li>
                            @foreach($divisions as $division)
                                <li>
                                    <a href="#{{ $division->abbreviation }}" class="smooth-scroll">
                                        <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}"/>
                                        {{ $division->name }}
                                        <span class="badge">{{ $division->sergeants->count() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
<script>
$(document).ready(function() {
    $('.leadership-table--division').each(function() {
        if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().destroy();
        }
        $(this).DataTable({
            paging: false,
            searching: false,
            info: false,
            columnDefs: [
                { targets: 1, visible: false },
                { targets: 2, orderData: 1 }
            ],
            order: [[1, 'desc']]
        });
    });

    var $navItems = $('.leadership-nav .nav-list a');

    function highlightNav() {
        var hash = window.location.hash;
        $navItems.parent().removeClass('nav-item--active');
        if (hash) {
            $navItems.filter('[href="' + hash + '"]').parent().addClass('nav-item--active');
        }
    }

    $(window).on('hashchange', highlightNav);
    highlightNav();
});
</script>
@endsection
