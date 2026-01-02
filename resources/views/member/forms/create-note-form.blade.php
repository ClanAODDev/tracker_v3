@include('application.partials.errors')
@php
    $canAssignTags = auth()->user()->can('assign', [\App\Models\DivisionTag::class, $member]);
    $assignableTags = $canAssignTags
        ? (new \App\Policies\DivisionTagPolicy)->getAssignableTags(auth()->user(), $member)->get()
        : collect();
@endphp
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">{{ $action }}</h4>
    </div>
    <div class="modal-body">
        <div class="form-group {{ $errors->has('type') ? ' has-error' : null }}">
            <label class="control-label">Note Type</label>
            <div class="note-type-selector">
                @foreach (\App\Models\Note::allNoteTypes() as $value => $label)
                    <label class="note-type-option {{ $value }}">
                        <input type="radio" name="type" value="{{ $value }}" {{ $loop->first ? 'checked' : '' }}>
                        <span class="note-type-card">
                            <span class="note-type-icon">
                                @if($value === 'positive')
                                    <i class="fa fa-thumbs-up"></i>
                                @elseif($value === 'negative')
                                    <i class="fa fa-thumbs-down"></i>
                                @elseif($value === 'sr_ldr')
                                    <i class="fas fa-shield-alt"></i>
                                @else
                                    <i class="fa fa-comment"></i>
                                @endif
                            </span>
                            <span class="note-type-label">{{ $label }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group {{ $errors->has('body') ? ' has-error' : null }}">
            <label for="body" class="control-label">Note Content</label>
            <textarea name="body" id="body" rows="4" class="form-control note-body-input"
                      placeholder="Enter your note here..." style="resize: vertical;">{{ old('body') }}</textarea>
            <div class="reminder-note-suggestion" style="display: none;">
                <div class="reminder-suggestion-content">
                    <i class="fa fa-lightbulb-o"></i>
                    <span>Tracking an inactivity reminder? Mark them as reminded instead of leaving a note.</span>
                </div>
                <div class="reminder-suggestion-actions">
                    @if(auth()->user()->member?->clan_id !== $member->clan_id)
                        <button type="button" class="btn btn-sm btn-success set-activity-reminder-btn" data-url="{{ route('member.set-activity-reminder', $member->clan_id) }}">
                            <i class="fa fa-bell"></i> Mark Reminded
                        </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-link dismiss-suggestion">Dismiss</button>
                </div>
            </div>
        </div>

        @if($canAssignTags && $assignableTags->isNotEmpty())
            <div class="form-group">
                <label for="tag_id" class="control-label">
                    <i class="fa fa-tag"></i> Add Tag <span class="text-muted">(optional)</span>
                </label>
                <select name="tag_id" id="tag_id" class="form-control">
                    <option value="">No tag</option>
                    @foreach($assignableTags as $tag)
                        <option value="{{ $tag->id }}" {{ old('tag_id') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }}{{ $tag->isGlobal() ? ' (Clan-wide)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-accent">
            <i class="fa fa-plus"></i> Add Note
        </button>
    </div>
</div>
