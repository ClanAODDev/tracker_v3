@if(@$alert = file_get_contents(base_path('maintenance.alert')))
    @if(request()->is('home') || request()->is('/'))
        <div class="container-fluid m-b-md">
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> {!! $alert !!}
            </div>
        </div>
    @endif
@endif
