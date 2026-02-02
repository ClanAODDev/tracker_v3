@if ($division->isShutdown())
    <x-notice type="danger">
        @if ($division->shutdown_at > now())
            This division will be shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should begin looking for new divisions.
        @else
            This division was shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should find new divisions or be removed immediately.
        @endif
    </x-notice>
@endif

@if ($pendingActions->divisionActions()->isNotEmpty())
    <div class="pending-actions pending-actions--desktop">
        <div class="pending-actions-header">
            <i class="fa fa-tasks"></i>
            <span>Action Items</span>
        </div>
        @foreach ($pendingActions->divisionActions() as $action)
            @if ($action->modalTarget)
                <a href="#" class="pending-action pending-action--{{ $action->style }}" data-toggle="modal" data-target="#{{ $action->modalTarget }}">
            @elseif ($action->key === 'unassigned-members')
                <a href="{{ $action->url }}" class="pending-action pending-action--{{ $action->style }} scroll-to-organize">
            @else
                <a href="{{ $action->url }}" class="pending-action pending-action--{{ $action->style }}">
            @endif
                <i class="{{ $action->iconClass() }}"></i>
                <span class="pending-action-count">{{ $action->count }}</span>
                <span class="pending-action-label">{{ Str::plural($action->label, $action->count) }}</span>
            </a>
        @endforeach
    </div>

    <div class="pending-actions pending-actions--mobile">
        <div class="pending-actions-header">
            <i class="fa fa-tasks"></i>
            <span>Action Items</span>
            <span class="pending-actions-total">{{ $pendingActions->divisionActions()->sum('count') }}</span>
        </div>
        <div class="dropdown">
            <button class="pending-actions-dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                <span>View Details</span>
                <i class="fa fa-chevron-down"></i>
            </button>
            <ul class="dropdown-menu pending-actions-dropdown">
                @foreach ($pendingActions->divisionActions() as $action)
                    <li>
                        @if ($action->modalTarget)
                            <a href="#" data-toggle="modal" data-target="#{{ $action->modalTarget }}">
                        @elseif ($action->key === 'unassigned-members')
                            <a href="{{ $action->url }}" class="scroll-to-organize">
                        @else
                            <a href="{{ $action->url }}">
                        @endif
                            <i class="{{ $action->iconClass() }}"></i>
                            <span class="pending-actions-dropdown-label">{{ Str::plural($action->label, $action->count) }}</span>
                            <span class="pending-actions-dropdown-count">{{ $action->count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@include('partials.no-squad-modal')
