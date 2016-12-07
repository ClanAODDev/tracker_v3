<form id="division-settings" method="post" class="well"
      action="{{ route('updateDivision', $division->abbreviation) }}">
    {{ method_field('PATCH') }}
    <fieldset>
        <legend><i class="fa fa-sliders"></i> General Settings
            <button type="submit" class="btn btn-success pull-right btn-xs">Save changes</button>
        </legend>


        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="division_structure" class="control-label">Division Structure</label>
                    <input type="number" id="division_structure" name="division_structure"
                           value="{{ $division->settings()->division_structure }}" class="form-control" required/>
                    <span class="help-block"><small>Numerical id of your division's division structure thread</small></span>
                </div>

            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="welcome_area" class="control-label">Welcome Area</label>
                    <input type="number" id="welcome_area" name="welcome_area"
                           value="{{ $division->settings()->welcome_area }}" class="form-control" required/>
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
            </div>
        </div>


        <div class="form-group">
            <label for="welcome_pm" class="control-label">Welcome PM</label>

            <textarea id="welcome_pm" name="welcome_pm" class="form-control"
                      rows="5">{{ $division->settings()->welcome_pm }}</textarea>
            <span class="help-block"><small><p>Message template provided to recruiters to send to new members following recruitment process. </p><p><code>%%member_name%%</code> is replaced with the member's name.</p></small></span>
        </div>

        {{ csrf_field() }}

    </fieldset>
</form>