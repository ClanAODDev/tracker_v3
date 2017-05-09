<form id="division-tags" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#division-tags">

    <div class="panel repeater">
        <div class="panel-heading"><h4>Division Tags</h4></div>
        <div class="panel-body">

            {{ method_field('PATCH') }}
            <div data-repeater-list="division_tags">
                @include('division.partials.division-tags')
            </div>
            {{ csrf_field() }}

        </div>

        <div class="pull-right">
            <button type="button" data-repeater-create class="btn btn-default">
                <i class="fa fa-plus fa-lg"></i> Add Tag
            </button>
            <button type="submit" class="btn btn-success">Save changes</button>
        </div>
    </div>
</form>
