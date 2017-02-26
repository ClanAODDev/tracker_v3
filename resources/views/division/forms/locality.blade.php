<form id="locality-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#locality-settings">

    {{ method_field('PATCH') }}

    <p>Customize the tracker's language to match your game's specific terminology. Entries should be all lower-case and singular. Use the
        <code>Old String</code> column as a guide to determine what each term should display as. Terms you wish to remain unchanged should match on both columns.
    </p>

    <table class="table">
        @include('division.partials.locality')
    </table>

    <div class="text-right">
        <button type="button" class="btn btn-default" data-reset-locality>
            <i class="fa fa-undo"></i> Reset to default
        </button>
        <button type="submit" class="btn btn-success">Save changes</button>
    </div>

    {{ csrf_field() }}

</form>