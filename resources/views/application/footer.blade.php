<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>

<script src="{{ asset('/js/main.js?v=3.3') }}"></script>

@include('application.partials.snow')
{{--@include('application.partials.confetti')--}}