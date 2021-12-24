@if(@$alert = file_get_contents(base_path('maintenance.alert')))
    <div class="alert alert-danger m-b-xl">
        <i class="fa fa-exclamation-triangle"></i> {!! $alert !!}
    </div>
@endif