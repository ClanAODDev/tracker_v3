@include('application.partials.errors')
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
            <textarea name="body" id="body" rows="4" class="form-control"
                      placeholder="Enter your note here..." style="resize: vertical;">{{ old('body') }}</textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-accent">
            <i class="fa fa-plus"></i> Add Note
        </button>
    </div>
</div>
