<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>

<script src="{{ asset('/js/main.js?v=3.2') }}"></script>

<script src="https://cdn.ravenjs.com/3.26.2/raven.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('/js/snowstorm.js') }}"></script>