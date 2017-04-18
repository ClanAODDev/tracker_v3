{!! Form::open(['method' => 'post', 'route' => ['storeNote', $member->clan_id]]) !!}
@include('application.partials.errors')
<div class="panel panel-filled">
    <div class="panel-body">
        <div class="form-group {{ $errors->has('body') ? ' has-error' : null }}">
            <label for="body" class="slight text-muted">Content</label>
            <textarea name="body" id="body" class="form-control"
                      rows="2" style="resize: vertical" required></textarea>
        </div>

        <div class="row">
            <div class="col-sm-4 form-group">
                <label for="type" class="slight text-muted">Note Type</label>
                <select name="type" id="type" class="form-control">
                    <option value="negative">Negative</option>
                    <option value="positive">Positive</option>
                    <option value="misc">Misc</option>

                    @if(auth()->user()->isRole(['sr_ldr', 'admin']))
                        <option value="sr_ldr">Sr Leaders Only</option>
                    @endif

                </select>
            </div>

            <div class="col-sm-4 form-group">
                <label for="forum_thread_id" class="slight text-muted">Forum Thread ID</label>
                <input type="number" class="form-control" name="forum_thread_id" id="forum_thread_id" />
            </div>

            <div class="col-xs-4 form-group">
                <button type="submit" class="btn btn-default btn-block" style="margin-top:23px">Submit</button>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!}