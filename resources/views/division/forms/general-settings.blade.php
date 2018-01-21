<form id="division-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#division-settings">
    {{ method_field('PATCH') }}

    <div class="col-md-6">
        <label for="division_structure" class="control-label">Division Structure</label>
        <input type="number" id="division_structure" name="division_structure" class="form-control"
               value="{{ $division->settings()->division_structure }}" required />

        <span class="help-block"><small>Numerical id of your division's division structure thread</small></span>
    </div>

    <div class="col-md-6">
        <label for="welcome_area" class="control-label">Welcome Area</label>
        <input type="number" id="welcome_area" name="welcome_area"
               value="{{ $division->settings()->welcome_area }}" class="form-control" required />

        <span class="help-block"><small>Numerical id of your division's welcome area.</small></span>
        <div class="checkbox">
            <label>
                <input type='hidden' value='0' name="use_welcome_thread">
                <input type="checkbox" name="use_welcome_thread"
                       {{ checked($division->settings()->use_welcome_thread) }}
                       id="use-welcome-thread"> Use thread instead of forum
            </label>
        </div>
    </div>

    <div class="col-md-6 {{ $errors->has('inactivity_days') ? 'has-error' : null }}">
        <label for="inactivity_days" class="control-label">Inactivity (days)</label>
        <input type="number" id="inactivity_days" name="inactivity_days"
               value="{{ $division->settings()->inactivity_days }}"
               class="form-control" required />
        <span class="help-block"><small>Number of days before a member is considered inactive.</small></span>
    </div>

    {{ csrf_field() }}

    <button type="submit" class="btn btn-success pull-right">Save changes</button>

</form>