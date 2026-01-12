<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    @csrf
</form>

@vite(['resources/assets/js/main.js'])

@include('application.partials.snow')
@include('application.partials.motes')
{{--@include('application.partials.confetti')--}}