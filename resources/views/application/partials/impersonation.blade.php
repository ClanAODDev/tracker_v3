<div class="container-fluid">
    <div class="alert alert-danger">
        <a href="{{ route('end-impersonation') }}" class="c-white">
            Currently impersonating user: <strong>{{ auth()->user()->name }}</strong>
        </a>
    </div>
</div>