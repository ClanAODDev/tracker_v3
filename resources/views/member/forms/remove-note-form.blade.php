<div class="panel panel-filled panel-c-danger collapsed">
    <div class="panel-heading panel-toggle">
        <div class="panel-tools">
            <i class="fa fa-chevron-up toggle-icon"></i>
        </div>
        <i class="fa fa-trash text-danger"></i> Destroy Note
    </div>

    <div class="panel-body">
        <p>
            Continue if you wish to delete this note from the member's record
        </p>
        {{ method_field('DELETE') }}

        {{ csrf_field() }}
    </div>
    <div class="panel-footer">
        <button type="submit" title="Destroy Note"
                class="btn btn-danger">Submit<span class="hidden-sm hidden-xs"> removal</span></button>
    </div>
</div>