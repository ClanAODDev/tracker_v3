@include('application.partials.errors')

<div class="panel panel-filled">
    <div class="panel-body">
        <div class="form-group {{ $errors->has('note-body') ? ' has-error' : null }}">
            <label for="note-body" class="slight text-muted">Content</label>
            <textarea name="note-body" id="note-body" class="form-control"
                      rows="2" style="resize: vertical" required></textarea>
        </div>

        <div class="row">
            <div class="col-sm-4 form-group">
                <label for="note-type" class="slight text-muted">Note Type</label>
                <select name="note-type" id="note-type" class="form-control">
                    <option value="negative">Negative</option>
                    <option value="positive">Positive</option>
                    <option value="misc">Misc</option>

                    @if(auth()->user()->isRole('admin'))
                        <option value="admin">Admin Only</option>
                    @endif

                </select>
            </div>

            <div class="col-sm-4 form-group">
                <label for="forum-thread" class="slight text-muted">Forum Thread ID</label>
                <input type="number" class="form-control" name="forum-thread" id="forum-thread" />
            </div>

            <div class="col-xs-4 form-group">
                <button type="submit" class="btn btn-default btn-block" style="margin-top:23px">Submit</button>
            </div>
        </div>
    </div>
</div>