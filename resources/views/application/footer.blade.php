<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script src="{{ asset('/js/main.js?v=3.4') }}"></script>

@include('application.partials.snow')
{{--@include('application.partials.confetti')--}}