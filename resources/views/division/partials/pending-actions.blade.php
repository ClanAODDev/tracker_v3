@if ($division->isShutdown())
    <x-notice type="danger">
        @if ($division->shutdown_at > now())
            This division will be shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should begin looking for new divisions.
        @else
            This division was shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should find new divisions or be removed immediately.
        @endif
    </x-notice>
@endif

@if ($pendingActions->hasAnyActions())
    <div class="pending-actions">
        @if ($pendingActions->memberRequests)
            <a href="{{ $pendingActions->memberRequestsUrl }}" class="pending-action pending-action--warning">
                <i class="fa fa-user-plus"></i>
                <span class="pending-action-count">{{ $pendingActions->memberRequests }}</span>
                <span class="pending-action-label">{{ Str::plural('Request', $pendingActions->memberRequests) }}</span>
            </a>
        @endif

        @if ($pendingActions->inactiveMembers)
            <a href="{{ $pendingActions->inactiveMembersUrl }}" class="pending-action pending-action--warning">
                <i class="fa fa-user-clock"></i>
                <span class="pending-action-count">{{ $pendingActions->inactiveMembers }}</span>
                <span class="pending-action-label">Outstanding Inactive</span>
            </a>
        @endif

        @if ($pendingActions->awardRequests)
            <a href="{{ $pendingActions->awardRequestsUrl }}" class="pending-action">
                <i class="fa fa-trophy"></i>
                <span class="pending-action-count">{{ $pendingActions->awardRequests }}</span>
                <span class="pending-action-label">{{ Str::plural('Award', $pendingActions->awardRequests) }}</span>
            </a>
        @endif

        @if ($pendingActions->pendingTransfers)
            <a href="{{ $pendingActions->pendingTransfersUrl }}" class="pending-action">
                <i class="fa fa-exchange-alt"></i>
                <span class="pending-action-count">{{ $pendingActions->pendingTransfers }}</span>
                <span class="pending-action-label">{{ Str::plural('Transfer', $pendingActions->pendingTransfers) }}</span>
            </a>
        @endif

        @if ($pendingActions->voiceIssues)
            <a href="{{ $pendingActions->voiceIssuesUrl }}" class="pending-action">
                <i class="fa fa-headset"></i>
                <span class="pending-action-count">{{ $pendingActions->voiceIssues }}</span>
                <span class="pending-action-label">Voice {{ Str::plural('Issue', $pendingActions->voiceIssues) }}</span>
            </a>
        @endif

        @if ($pendingActions->unassignedMembers)
            <a href="{{ $pendingActions->unassignedMembersUrl }}" class="pending-action scroll-to-organize">
                <i class="fa fa-user-slash"></i>
                <span class="pending-action-count">{{ $pendingActions->unassignedMembers }}</span>
                <span class="pending-action-label">No Platoon</span>
            </a>
        @endif

        @if ($pendingActions->unassignedToSquad)
            <a href="#" class="pending-action" data-toggle="modal" data-target="#no-squad-modal">
                <i class="fa fa-users-slash"></i>
                <span class="pending-action-count">{{ $pendingActions->unassignedToSquad }}</span>
                <span class="pending-action-label">No Squad</span>
            </a>
        @endif
    </div>
@endif

@if ($pendingActions->unassignedToSquad)
    <div class="modal fade" id="no-squad-modal" tabindex="-1" role="dialog"
         data-url="{{ route('division.unassigned-to-squad', $division) }}">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Members Without Squad Assignment</h4>
                </div>
                <div class="modal-body">
                    <div id="no-squad-loading" class="text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
                    <div id="no-squad-list" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
