<div class="container-fluid">
    <div class="alert alert-danger slight">
        Currently impersonating user: <strong>{{ auth()->user()->name }}</strong>.
        <a href="{{ route('end-impersonation') }}" class="c-white alert-link">
            End impersonation?
        </a>
        <i class="fa fa-user-secret pull-right fa-2x"></i>
    </div>
</div>