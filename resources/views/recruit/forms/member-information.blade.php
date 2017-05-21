<div class="panel panel-filled">
    <div class="panel-heading">
        <strong class="text-uppercase">Information</strong>
    </div>
    <div class="panel-body">

        <p>Please be careful and ensure member information is entered accurately. Forum names can be changed when the member status request is submitted, and changed names will sync with the tracker automatically.</p>

        <div class="row">

            <div class="col-md-4 form-group {{ $errors->has('member_id') ? ' has-error' : null }}">
                <label for="name">Forum Member Id</label>
                <input type="number" class="form-control" name="member_id"
                       id="member_id" value="{{ old('member_id') }}" required>
                @if ($errors->has('member_id'))
                    <span class="help-block"><small>{{ $errors->first('member_id') }}</small></span>
                @endif
            </div>

            <div class="col-md-4 form-group">
                <label for="forum_name">Forum Name</label>
                <input type="text" class="form-control" value="{{ old('forum_name') }}"
                       name="forum_name" id="forum_name" required>
            </div>

            <div class="col-md-4 form-group">
                <label for="ingame_name">{{ $division->handle->name or "Ingame Name" }}</label>
                <input type="text" class="form-control" name="ingame_name"
                       value="{{ old('ingame_name') }}" id="ingame_name" required>
            </div>

        </div>
    </div>
</div>