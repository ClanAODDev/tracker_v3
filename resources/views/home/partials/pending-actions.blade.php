@if ($pendingActions->hasAnyActions())
    <div class="pending-actions pending-actions--desktop animate-fade-in-up" style="animation-delay: 0.1s">
        <div class="pending-actions-header">
            <i class="fa fa-tasks"></i>
            <span>Action Items</span>
        </div>
        @foreach ($pendingActions->actions as $action)
            @if ($action->modalTarget)
                <a href="#" class="pending-action pending-action--{{ $action->style }}" data-toggle="modal" data-target="#{{ $action->modalTarget }}">
            @else
                <a href="{{ $action->url }}" class="pending-action pending-action--{{ $action->style }}">
            @endif
                <i class="fa {{ $action->icon }}"></i>
                <span class="pending-action-count">{{ $action->count }}</span>
                <span class="pending-action-label">{{ Str::plural($action->label, $action->count) }}</span>
            </a>
        @endforeach
    </div>

    <div class="pending-actions pending-actions--mobile animate-fade-in-up" style="animation-delay: 0.1s">
        <div class="pending-actions-header">
            <i class="fa fa-tasks"></i>
            <span>Action Items</span>
            <span class="pending-actions-total">{{ $pendingActions->total() }}</span>
        </div>
        <div class="dropdown">
            <button class="pending-actions-dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                <span>View Details</span>
                <i class="fa fa-chevron-down"></i>
            </button>
            <ul class="dropdown-menu pending-actions-dropdown">
                @foreach ($pendingActions->actions as $action)
                    <li>
                        @if ($action->modalTarget)
                            <a href="#" data-toggle="modal" data-target="#{{ $action->modalTarget }}">
                        @else
                            <a href="{{ $action->url }}">
                        @endif
                            <i class="fa {{ $action->icon }}"></i>
                            <span class="pending-actions-dropdown-label">{{ Str::plural($action->label, $action->count) }}</span>
                            <span class="pending-actions-dropdown-count">{{ $action->count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@include('partials.no-squad-modal', ['division' => $myDivision])
