<div class="panel">
    <div class="panel-body">
        <form id="default-tags" method="post"
              action="#">

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