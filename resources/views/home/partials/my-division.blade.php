<div class="my-division-card animate-fade-in-up">
    <div class="my-division-header">
        <div class="my-division-info">
            <img src="{{ $myDivision->getLogoPath() }}" alt="{{ $myDivision->name }}" class="my-division-logo">
            <div class="my-division-text">
                <h2 class="my-division-title">
                    <a href="{{ route('division', $myDivision->slug) }}">{{ $myDivision->name }}</a>
                </h2>
                <span class="my-division-members">{{ $myDivision->members->count() }} Members</span>
            </div>
        </div>
        @can('update', $myDivision)
            <a href="{{ route('filament.mod.resources.divisions.edit', $myDivision) }}" class="btn btn-accent">
                <i class="fa fa-cog"></i>
                <span>Manage</span>
            </a>
        @endcan
    </div>

    <div class="my-division-actions">
        <a href="{{ route('division', $myDivision->slug) }}" class="action-card">
            <i class="fa fa-home"></i>
            <span>Division Home</span>
        </a>
        <a href="{{ route('division.members', $myDivision) }}" class="action-card">
            <i class="fa fa-users"></i>
            <span>Members</span>
        </a>
        <a href="{{ route('division.structure', $myDivision) }}" class="action-card">
            <i class="fa fa-sitemap"></i>
            <span>Structure</span>
        </a>
        @can('recruit', App\Models\Member::class)
            @if (!$myDivision->isShutdown())
                <a href="{{ route('recruiting.form', $myDivision) }}" class="action-card action-card-accent">
                    <i class="fa fa-user-plus"></i>
                    <span>Add Recruit</span>
                </a>
            @endif
        @endcan
        <div class="action-card-dropdown">
            <div class="action-card action-card-trigger">
                <i class="fa fa-chart-bar"></i>
                <span>Reports</span>
                <i class="fa fa-chevron-down action-card-arrow"></i>
            </div>
            <div class="action-card-menu">
                <a href="{{ route('division.census', $myDivision) }}" class="action-card-menu-item">
                    <i class="fa fa-chart-line"></i> Census
                </a>
                <a href="{{ route('division.retention-report', $myDivision) }}" class="action-card-menu-item">
                    <i class="fa fa-chart-area"></i> Retention
                </a>
                <a href="{{ route('division.promotions', $myDivision) }}" class="action-card-menu-item">
                    <i class="fa fa-medal"></i> Promotions
                </a>
                <a href="{{ route('division.voice-report', $myDivision) }}" class="action-card-menu-item">
                    <i class="fa fa-headset"></i> Voice
                </a>
            </div>
        </div>
        <a href="{{ route('division.inactive-members', $myDivision) }}" class="action-card">
            <i class="fa fa-user-clock"></i>
            <span>Inactives</span>
        </a>
        <a href="{{ route('awards.index', ['division' => $myDivision->slug]) }}" class="action-card">
            <i class="fa fa-trophy"></i>
            <span>Awards</span>
        </a>
        @can('create', \App\Models\Leave::class)
            <a href="{{ route('filament.mod.resources.leaves.index') }}" class="action-card">
                <i class="fa fa-calendar-alt"></i>
                <span>Leave</span>
            </a>
        @endcan
        @can ('show', App\Models\Note::class)
            <a href="{{ route('division.notes', $myDivision) }}" class="action-card">
                <i class="fa fa-sticky-note"></i>
                <span>Notes</span>
            </a>
        @endcan
    </div>
</div>
