<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<div id="ticket-modal-container"></div>

@vite(['resources/assets/js/main.js', 'resources/assets/js/tickets.js'])

@include('application.partials.snow')
@include('application.partials.motes')
{{--@include('application.partials.confetti')--}}