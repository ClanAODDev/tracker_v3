<div class="form-horizontal">
    <fieldset>
        <legend>Recruitment Settings</legend>
        <div class="form-group">
            <label for="division_structure" class="col-lg-3 control-label">Division Structure</label>
            <div class="col-lg-9">
                <input type="number" id="division_structure" name="division_structure"
                       value="{{ $division->settings()->division_structure }}" class="form-control" required/>
                <span class="help-block"><small>Numerical id of your division's division structure thread</small></span>
            </div>
        </div>

        <div class="form-group">
            <label for="welcome_area" class="col-lg-3 control-label">Welcome Area</label>
            <div class="col-lg-9">
                <input type="number" id="welcome_area" name="welcome_area"
                       value="{{ $division->settings()->welcome_area }}" class="form-control" required/>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="use_welcome_thread"
                               {{ checked($division->settings()->use_welcome_thread) }}
                               id="use-welcome-thread"> Use thread instead of forum
                    </label>
                </div>
                <span class="help-block"><small>Numerical id of your division's welcome area. <strong>Note</strong>: If your division uses a thread instead of a subforum, be sure to enable the option above.</small></span>
            </div>
        </div>

        <div class="form-group">
            <label for="welcome_pm" class="col-lg-3 control-label">Welcome PM</label>
            <div class="col-lg-9">
                            <textarea id="welcome_pm" name="welcome_pm"
                                      value="{{ $division->settings()->welcome_pm }}"
                                      class="form-control" rows="5"></textarea>
                <span class="help-block"><small><p>Message template provided to recruiters to send to new members following recruitment process. </p><p><code>%%member_name%%</code> is replaced with the member's name.</p></small></span>
            </div>
        </div>

    </fieldset>
</div>

