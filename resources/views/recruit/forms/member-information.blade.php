<div class="panel panel-filled">
    <div class="panel-heading">
        <strong class="text-uppercase">Information</strong>
    </div>
    <div class="panel-body">

        <p>Please be careful and ensure member information is entered accurately. Forum names can be changed when the member status request is submitted, and changed names will sync with the tracker automatically.</p>

        <div class="row">

            <div class="col-md-4 form-group {{ $errors->has('member-id') ? ' has-error' : null }}">
                <label for="name">Forum Member Id</label>
                <input type="number" class="form-control" name="member-id"
                       id="member-id" value="{{ old('member-id') }}" required>
                @if ($errors->has('member-id'))
                    <span class="help-block"><small>{{ $errors->first('member-id') }}</small></span>
                @endif
            </div>

            <div class="col-md-4 form-group">
                <label for="forum-name">Forum Name</label>
                <input type="text" class="form-control" value="{{ old('forum-name') }}"
                       name="forum-name" id="forum-name" required>
            </div>

            <div class="col-md-4 form-group">
                <label for="ingame-name">{{ $division->handle->name or "Ingame Name" }}</label>
                <input type="text" class="form-control" name="ingame-name"
                       value="{{ old('ingame-name') }}" id="ingame-name" required>
            </div>

        </div>
    </div>
</div>