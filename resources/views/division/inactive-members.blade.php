@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $division->name }}
        @endslot
        @slot ('subheading')
            <span class="hidden-xs">Inactive Members</span>
            <span class="visible-xs">Inactive</span>
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('inactive-members', $division) !!}

        <div class="inactive-stats">
            <div class="inactive-stat">
                <div class="inactive-stat-value">{{ $stats['total'] }}</div>
                <div class="inactive-stat-label">Inactive</div>
            </div>
            <div class="inactive-stat inactive-stat--warning">
                <div class="inactive-stat-value">{{ $stats['flagged'] }}</div>
                <div class="inactive-stat-label">Flagged</div>
            </div>
            <div class="inactive-stat inactive-stat--danger">
                <div class="inactive-stat-value">{{ $stats['severe'] }}</div>
                <div class="inactive-stat-label">Severe (2x threshold)</div>
            </div>
            <div class="inactive-stat inactive-stat--info">
                <div class="inactive-stat-value">{{ $division->settings()->inactivity_days }}d</div>
                <div class="inactive-stat-label">Threshold</div>
            </div>
        </div>

        <div class="inactive-toolbar">
            <div class="inactive-filters">
                <div class="inactive-filter-group">
                    <label class="inactive-filter-label">
                        <i class="fa fa-filter"></i> {{ $division->locality('platoon') }}
                    </label>
                    <select class="inactive-filter-select" onchange="if(this.value) window.location.href=this.value">
                        <option value="{{ route($requestPath, $division->slug) }}">All {{ $division->locality('platoon') }}s</option>
                        @foreach ($division->platoons as $platoon)
                            <option
                                value="{{ route($requestPath, [$division->slug, $platoon->id]) }}"
                                {{ request()->platoon && request()->platoon->id == $platoon->id ? 'selected' : '' }}>
                                {{ $platoon->name }}
                                @if(isset($stats['byPlatoon'][$platoon->id]))
                                    ({{ $stats['byPlatoon'][$platoon->id] }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="inactive-toolbar-right">
                @can('remindActivity', \App\Models\Member::class)
                    <button type="button" class="btn btn-default inactive-bulk-toggle">
                        Bulk Mode
                    </button>
                @endcan
                <div class="inactive-search-wrapper">
                    <i class="fa fa-search inactive-search-icon"></i>
                    <input type="text"
                           id="inactive-search"
                           placeholder="Search members..."
                           class="inactive-search-input">
                </div>
            </div>
        </div>

        @include('division.partials.inactive-bulk-bar')

        <div class="inactive-tabs">
            <button class="inactive-tab active" data-tab="inactive">
                <i class="fab fa-discord"></i>
                <span>Discord Inactive</span>
                <span class="inactive-tab-count">{{ count($inactiveDiscordMembers) }}</span>
            </button>
            <button class="inactive-tab" data-tab="flagged">
                <i class="fa fa-flag"></i>
                <span>Flagged</span>
                <span class="inactive-tab-count">{{ count($flaggedMembers) }}</span>
            </button>
        </div>

        <div class="inactive-content">
            <div class="inactive-panel active" data-panel="inactive">
                @include('division.partials.inactive-members', ['type' => 'discord'])
            </div>
            <div class="inactive-panel" data-panel="flagged">
                @include('division.partials.flagged-members')
            </div>
        </div>

        @if(count($flagActivity))
            <div class="inactive-activity-log">
                <div class="inactive-activity-header">
                    <i class="fa fa-history"></i>
                    <span>Recent Activity</span>
                </div>
                <div class="inactive-activity-list">
                    @foreach ($flagActivity as $activity)
                        @if (isset($activity->subject->name))
                            @include('division.partials.inactive-activity-log-entry')
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

@endsection
