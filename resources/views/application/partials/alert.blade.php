@if(session('impersonating') || auth()->user()->isImpersonatingRole())
    @include('application.partials.impersonation')
@endif

@if(@$alert = file_get_contents(base_path('maintenance.alert')))
    @if(request()->is('home') || request()->is('/'))
        <div class="container-fluid m-b-md">
            <x-notice type="danger">
                {!! $alert !!}
            </x-notice>
        </div>
    @endif
@endif
