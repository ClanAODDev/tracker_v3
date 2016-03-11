<nav class="navbar navbar-default navbar-nav navbar-fixed-top">

    <div class="container">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        @if (Auth::check())
            @include('partials.nav.authed')
        @endif

    </div>
</nav>
