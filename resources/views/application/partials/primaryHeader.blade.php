<div class="container-fluid">
    <div class="navbar-header">
        <div id="mobile-menu">
            <div class="left-nav-toggle">
                <a href="#">
                    <i class="stroke-hamburgermenu"></i>
                </a>
            </div>
        </div>
        <a class="navbar-brand" href="{{ route('home') }}">
            TRACKER
            <span>v3.2.0</span>
        </a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <div class="left-nav-toggle">
            <a href="#">
                <i class="stroke-hamburgermenu"></i>
            </a>
        </div>

        <div class="navbar-form navbar-left">
            <img src="{{ asset('images/loading_2.gif') }}" alt="Results loading..."
                 class="hidden results-loader" style="opacity: .5" />
            <input type="text" class="form-control" id="member-search" name="search"
                   placeholder="Search for a player..." />
            <span id="searchclear" class="fa fa-times-circle fa-2x text-muted pull-right"></span>
        </div>
    </div>
</div>