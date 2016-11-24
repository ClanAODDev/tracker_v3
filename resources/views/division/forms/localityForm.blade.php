<div class="well">
    <fieldset>
        <legend><i class="fa fa-language"></i> Locality</legend>

        <form id="locality-settings" method="post"
              action="{{ action('DivisionController@update', $division->abbreviation) }}">
            {{ method_field('PATCH') }}


            <div class="form-group">
                <label for="division_structure" class="control-label">Division Structure</label>
                <input type="number" id="division_structure" name="division_structure"
                       value="{{ $division->settings()->division_structure }}" class="form-control" required/>
                <span class="help-block"><small>Numerical id of your division's division structure thread</small></span>
            </div>


            <div class="form-group margin-top-50">
                <button type="submit" class="btn btn-default btn-lg pull-right">Save changes</button>
            </div>

            {{ csrf_field() }}
        </form>

    </fieldset>
</div>

