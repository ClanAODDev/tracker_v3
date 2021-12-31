@if(@$alert = file_get_contents(base_path('maintenance.alert')))
    <div class="alert alert-danger m-b-xl">
        <i class="fa fa-exclamation-triangle"></i> {!! $alert !!}
    </div>
@endif

{{-- New Years --}}
@if(request()->is('home', ''))
    <div class="alert alert-default m-b-xl">
        &#127881; Have a safe and happy New Year, leaders of AOD!
        @if(getSnowSetting() && getSnowSetting() != 'no_snow' && !request()->is('settings'))
            <span class="pull-right">Too much confetti?
        <a href="{{ route('user.settings.show') }}" class="btn btn-accent btn-xs">Turn it off here</a></span>
        @endif
    </div>
@endif
