@include('application.partials.errors')
@if (isset($note) && $note->leave && !isset($create))
    <div class="alert alert-warning">
        <strong>Note:</strong> This note is associated with a leave request and cannot be deleted until the LOA is
        revoked.
        <a class="alert-link"
           href="{{ route('leave.edit', [$member->clan_id, $note->leave->id]) }}">View LOA</a></div>
@endif
<div class="panel panel-filled">
    <div class="panel-heading">{{ $action }}</div>
    <div class="panel-body">
        <div class="form-group {{ $errors->has('body') ? ' has-error' : null }}">
            <label for="body" class="slight text-muted">Content</label>
            <textarea name="body" id="body" rows="2" class="form-control">{{ $note->body ?? null }}</textarea>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-4 form-group">
                <label for="type" class="slight text-muted">Note Type</label>
                <select name="type" id="type" class="form-control">
                    @foreach (\App\Models\Note::allNoteTypes() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-4 form-group">
                <label for="forum_thread_id" class="slight text-muted">Forum Thread Id</label>
                <input type="number" name="forum_thread_id" id="forum_thread_id" class="form-control">
            </div>

            <div class="col-xs-4 form-group">
                <button type="submit" class="btn btn-default btn-block" style="margin-top:23px">Submit</button>
            </div>
        </div>
    </div>
</div>