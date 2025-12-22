@include('application.partials.errors')

<div id="selected-data" class="bulk-action-bar">
    <div class="bulk-action-bar-content">
        <span class="status-text"></span>
        <div class="actions">
            <form action="{{ route('private-message.create', compact('division')) }}" method="POST">
                <input type="hidden" id="pm-member-data" name="pm-member-data">
                <input type="hidden" id="tag-member-data" name="tag-member-data">
                @csrf
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-bullhorn text-accent"></i> <span class="hidden-xs hidden-sm">Send PM</span>...
                </button>
            </form>
            @can('assign', App\Models\DivisionTag::class)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#bulk-tags-modal">
                    <i class="fa fa-tags text-accent"></i> <span class="hidden-xs hidden-sm">Tags</span>...
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
