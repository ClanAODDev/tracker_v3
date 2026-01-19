@include('application.partials.errors')

<div id="selected-data" class="bulk-action-bar">
    <div class="bulk-action-bar-content">
        <span class="status-text"></span>
        <div class="actions">
            @can('remindActivity', \App\Models\Member::class)
                <form action="{{ route('private-message.create', compact('division')) }}" method="POST" class="bulk-pm-form">
                    <input type="hidden" id="pm-member-data" name="pm-member-data">
                    <input type="hidden" id="tag-member-data" name="tag-member-data">
                    <input type="hidden" id="transfer-member-data" name="transfer-member-data">
                    @csrf
                    <button type="submit" class="btn btn-default">
                        <i class="fa fa-bullhorn text-accent"></i> <span class="hidden-xs hidden-sm">Send PM</span>...
                    </button>
                </form>
            @endcan
            @can('assign', App\Models\DivisionTag::class)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#bulk-tags-modal">
                    <i class="fa fa-tags text-accent"></i> <span class="hidden-xs hidden-sm">Tags</span>...
                </button>
            @endcan
            @can('manageUnassigned', App\Models\User::class)
                <button type="button" id="bulk-transfer-btn" class="btn btn-default" data-toggle="modal" data-target="#bulk-transfer-modal">
                    <i class="fa fa-exchange-alt text-accent"></i> <span class="hidden-xs hidden-sm">Transfer</span>...
                </button>
            @endcan
            @can('remindActivity', \App\Models\Member::class)
                <button type="button" id="bulk-reminder-btn" class="btn btn-default"
                        data-url="{{ route('bulk-reminder.store', $division) }}"
                        title="Mark as reminded">
                    <i class="fa fa-bell text-accent"></i> <span class="hidden-xs hidden-sm">Reminder</span>
                </button>
            @endcan
            <button type="button" class="btn btn-link bulk-action-close" title="Clear selection">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>

@can('assign', App\Models\DivisionTag::class)
@php
    $assignableTags = \App\Models\DivisionTag::forDivision($division->id)
        ->assignableBy()
        ->get();
@endphp
<div class="modal fade" id="bulk-tags-modal" tabindex="-1" role="dialog" data-store-url="{{ route('bulk-tags.store', $division) }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Manage Tags</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="bulk-tags-member-count"></p>
                @if($assignableTags->isEmpty())
                    <p class="text-muted">No tags available for this division.</p>
                @else
                    <div class="bulk-tags-inline">
                        @foreach($assignableTags as $tag)
                            <label class="bulk-tag-label">
                                <input type="checkbox" class="bulk-tag-checkbox" value="{{ $tag->id }}">
                                <span class="badge bulk-tag-badge tag-visibility-{{ $tag->visibility->value }}">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                @if($assignableTags->isNotEmpty())
                    <button type="button" id="bulk-remove-tags" class="btn btn-danger" disabled>
                        <i class="fa fa-minus"></i> Remove
                    </button>
                    <button type="button" id="bulk-assign-tags" class="btn btn-accent" disabled>
                        <i class="fa fa-plus"></i> Assign
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endcan

@can('manageUnassigned', App\Models\User::class)
<div class="modal fade" id="bulk-transfer-modal" tabindex="-1" role="dialog"
     data-platoons-url="{{ route('bulk-transfer.platoons', $division) }}"
     data-store-url="{{ route('bulk-transfer.store', $division) }}"
     data-parttimers="{{ !empty($includeParttimers) ? 'true' : 'false' }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Transfer Members</h4>
            </div>
            <div class="modal-body">
                <div id="bulk-transfer-parttimer-warning" class="alert alert-danger" style="display: none;">
                    <i class="fa fa-exclamation-triangle"></i>
                    You cannot transfer members when part-timers are included in the listing. Part-time members belong to other divisions and cannot be reassigned here.
                </div>

                <div id="bulk-transfer-form-content">
                    <p class="text-muted" id="bulk-transfer-member-count"></p>

                    <div class="form-group">
                        <label for="transfer-platoon">{{ $division->locality('Platoon') }}</label>
                        <select id="transfer-platoon" class="form-control">
                            <option value="">Select {{ $division->locality('platoon') }}...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="transfer-squad">{{ $division->locality('Squad') }} <span class="text-muted">(optional)</span></label>
                        <select id="transfer-squad" class="form-control" disabled>
                            <option value="">No {{ $division->locality('squad') }} assignment</option>
                        </select>
                        <p class="help-block text-muted">Leave unselected to assign only to the {{ $division->locality('platoon') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="bulk-transfer-submit" class="btn btn-accent" disabled>
                    <i class="fa fa-exchange-alt"></i> Transfer
                </button>
            </div>
        </div>
    </div>
</div>
@endcan
