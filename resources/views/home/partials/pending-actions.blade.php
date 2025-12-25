@if ($pendingActions->hasAnyActions())
    <div class="pending-actions animate-fade-in-up" style="animation-delay: 0.1s">
        <div class="pending-actions-header">
            <i class="fa fa-tasks"></i>
            <span>Pending Actions</span>
        </div>
        <div class="pending-actions-badges">
            @if ($pendingActions->memberRequests)
                <a href="{{ $pendingActions->memberRequestsUrl }}" class="action-badge action-badge--warning">
                    <i class="fa fa-user-plus"></i>
                    <span class="action-badge-count">{{ $pendingActions->memberRequests }}</span>
                    <span class="action-badge-label">{{ Str::plural('Request', $pendingActions->memberRequests) }}</span>
                </a>
            @endif

            @if ($pendingActions->inactiveMembers)
                <a href="{{ $pendingActions->inactiveMembersUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-user-clock"></i>
                    <span class="action-badge-count">{{ $pendingActions->inactiveMembers }}</span>
                    <span class="action-badge-label">{{ Str::plural('Inactive', $pendingActions->inactiveMembers) }}</span>
                </a>
            @endif

            @if ($pendingActions->awardRequests)
                <a href="{{ $pendingActions->awardRequestsUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-trophy"></i>
                    <span class="action-badge-count">{{ $pendingActions->awardRequests }}</span>
                    <span class="action-badge-label">{{ Str::plural('Award', $pendingActions->awardRequests) }}</span>
                </a>
            @endif

            @if ($pendingActions->clanAwardRequests)
                <a href="{{ $pendingActions->clanAwardRequestsUrl }}" class="action-badge action-badge--accent">
                    <i class="fa fa-globe"></i>
                    <span class="action-badge-count">{{ $pendingActions->clanAwardRequests }}</span>
                    <span class="action-badge-label">Clan {{ Str::plural('Award', $pendingActions->clanAwardRequests) }}</span>
                </a>
            @endif

            @if ($pendingActions->pendingTransfers)
                <a href="{{ $pendingActions->pendingTransfersUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-exchange-alt"></i>
                    <span class="action-badge-count">{{ $pendingActions->pendingTransfers }}</span>
                    <span class="action-badge-label">{{ Str::plural('Transfer', $pendingActions->pendingTransfers) }}</span>
                </a>
            @endif

            @if ($pendingActions->pendingLeaves)
                <a href="{{ $pendingActions->pendingLeavesUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-calendar-alt"></i>
                    <span class="action-badge-count">{{ $pendingActions->pendingLeaves }}</span>
                    <span class="action-badge-label">{{ Str::plural('LOA', $pendingActions->pendingLeaves) }}</span>
                </a>
            @endif

            @if ($pendingActions->voiceIssues)
                <a href="{{ $pendingActions->voiceIssuesUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-headset"></i>
                    <span class="action-badge-count">{{ $pendingActions->voiceIssues }}</span>
                    <span class="action-badge-label">Voice {{ Str::plural('Issue', $pendingActions->voiceIssues) }}</span>
                </a>
            @endif

            @if ($pendingActions->unassignedMembers)
                <a href="{{ $pendingActions->unassignedMembersUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-user-slash"></i>
                    <span class="action-badge-count">{{ $pendingActions->unassignedMembers }}</span>
                    <span class="action-badge-label">No Platoon</span>
                </a>
            @endif

            @if ($pendingActions->unassignedToSquad)
                <a href="{{ $pendingActions->unassignedToSquadUrl }}" class="action-badge action-badge--info">
                    <i class="fa fa-users-slash"></i>
                    <span class="action-badge-count">{{ $pendingActions->unassignedToSquad }}</span>
                    <span class="action-badge-label">No Squad</span>
                </a>
            @endif
        </div>
    </div>
@endif
