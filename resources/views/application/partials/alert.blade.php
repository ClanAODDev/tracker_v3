@if(@$alert = file_get_contents(base_path('maintenance.alert')))
    <div class="container-fluid">
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> {!! $alert !!}
        </div>
    </div>
@endif
