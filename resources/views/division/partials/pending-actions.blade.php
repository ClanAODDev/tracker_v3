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
        @foreach ($pendingActions->actions as $action)
            @if ($action->modalTarget)
                <a href="#" class="pending-action pending-action--{{ $action->style }}" data-toggle="modal" data-target="#{{ $action->modalTarget }}">
            @elseif ($action->key === 'unassigned-members')
                <a href="{{ $action->url }}" class="pending-action pending-action--{{ $action->style }} scroll-to-organize">
            @else
                <a href="{{ $action->url }}" class="pending-action pending-action--{{ $action->style }}">
            @endif
                <i class="fa {{ $action->icon }}"></i>
                <span class="pending-action-count">{{ $action->count }}</span>
                <span class="pending-action-label">{{ Str::plural($action->label, $action->count) }}</span>
            </a>
        @endforeach
    </div>
@endif

@if ($pendingActions->has('unassigned-to-squad'))
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
