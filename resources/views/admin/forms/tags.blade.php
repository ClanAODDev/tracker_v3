<div class="panel">
    <div class="panel-body">
        <p>Default tags are tags provided to all divisions to prevent duplication. If a division-created tag is used in multiple divisions, then that is a good indication that it should be converted to a default tag (and all instances of the tag label switched)</p>

        <form id="default-tags" method="post" action="{{ route('adminUpdateTags') }}">

            <div class="panel repeater">
                <div class="panel-body">

                    {{ method_field('PATCH') }}
                    <div data-repeater-list="default_tags" class="row">
                        @include('admin.partials.tags')
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
    </div>
</div>