<form id="locality-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#locality-settings">

    {{ method_field('PATCH') }}
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