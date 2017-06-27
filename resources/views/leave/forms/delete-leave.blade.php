<div class="panel panel-filled panel-c-danger collapsed">
    <div class="panel-heading panel-toggle">
        <div class="panel-tools">
            <i class="fa fa-chevron-up toggle-icon"></i>
        </div>
        <i class="fa fa-trash text-danger"></i> Revoke Leave
    </div>
    <div class="panel-body">
        <p>Revoking leave will only affect the leave itself. The note that was generated for the LOA request will persist, for posterity.</p>
    </div>
    <div class="panel-footer">
        <button type="submit" class="btn btn-danger">Revoke LOA</button>
    </div>
</div>

{{ csrf_field() }}
