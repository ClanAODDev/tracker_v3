<div class="division-tools">
    <a href="{{ route('division.members', $division) }}" class="tool-card">
        <i class="fa fa-users"></i>
        <span>Members</span>
    </a>

    @can('recruit', App\Models\Member::class)
        @if (!$division->isShutdown())
            <a href="{{ route('recruiting.form', $division) }}" class="tool-card tool-card-accent">
                <i class="fa fa-user-plus"></i>
                <span>Add Recruit</span>
            </a>
        @endif
    @endcan

    <div class="tool-card-dropdown">
        <div class="tool-card tool-card-trigger">
            <i class="fa fa-chart-bar"></i>
            <span>Reports</span>
            <i class="fa fa-chevron-down tool-card-arrow"></i>
        </div>
        <div class="tool-card-menu">
            <a href="{{ route('division.census', $division) }}" class="tool-card-menu-item">
                <i class="fa fa-chart-line"></i> Census
            </a>
            <a href="{{ route('division.retention-report', $division) }}" class="tool-card-menu-item">
                <i class="fa fa-chart-area"></i> Retention
            </a>
            <a href="{{ route('division.promotions', $division) }}" class="tool-card-menu-item">
                <i class="fa fa-medal"></i> Promotions
            </a>
            <a href="{{ route('division.voice-report', $division) }}" class="tool-card-menu-item">
                <i class="fa fa-headset"></i> Voice
            </a>
        </div>
    </div>

    <a href="{{ route('division.inactive-members', $division) }}" class="tool-card">
        <i class="fa fa-user-clock"></i>
        <span>Inactives</span>
    </a>

    <a href="{{ route('partTimers', $division) }}" class="tool-card">
        <i class="fa fa-user-tag"></i>
        <span>Part Timers</span>
    </a>

    <a href="{{ route('division.org-chart', $division) }}" class="tool-card">
        <i class="fa fa-sitemap"></i>
        <span>Org Chart</span>
    </a>

    <a href="{{ route('awards.index', ['division' => $division->slug]) }}" class="tool-card">
        <i class="fa fa-trophy"></i>
        <span>Awards</span>
    </a>

    @can('create', \App\Models\Leave::class)
        <a href="{{ route('filament.mod.resources.leaves.index') }}" class="tool-card">
            <i class="fa fa-calendar-alt"></i>
            <span>Leave</span>
        </a>
    @endcan

    @can ('show', App\Models\Note::class)
        <a href="{{ route('division.notes', $division) }}" class="tool-card">
            <i class="fa fa-sticky-note"></i>
            <span>Notes</span>
        </a>
    @endcan

    @can('manage', \App\Models\MemberRequest::class)
        <a href="{{ route('filament.mod.resources.member-requests.index') }}" class="tool-card">
            <i class="fa fa-inbox"></i>
            <span>Requests</span>
        </a>
    @endcan
</div>