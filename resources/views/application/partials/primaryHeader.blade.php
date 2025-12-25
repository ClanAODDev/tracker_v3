<div class="container-fluid">
    <div class="navbar-header">
        <div id="mobile-menu" class="hidden-xs">
            <div class="left-nav-toggle">
                <a href="#">
                    <i class="stroke-hamburgermenu"></i>
                </a>
            </div>
        </div>
        <a class="navbar-brand" href="{{ route('home') }}">
            TRACKER
            <span>{{ config('app.version') }}</span>
        </a>
        <button class="mobile-help-toggle visible-xs" title="Help Center" onclick="window.openTicketModal && window.openTicketModal()">
            Help
            <span class="help-badge" style="display: none;"></span>
        </button>
        <button class="mobile-settings-toggle visible-xs" title="Settings">
            <i class="fa fa-cog"></i>
        </button>
        <button class="mobile-search-toggle visible-xs">
            <i class="fa fa-search"></i>
        </button>
        <button class="mobile-nav-toggle visible-xs">
            <i class="fa fa-bars"></i>
        </button>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <div class="left-nav-toggle hidden-xs">
            <a href="#">
                <i class="stroke-hamburgermenu"></i>
            </a>
        </div>

        <div class="navbar-form navbar-left hidden-xs">
            <div class="desktop-search-wrap">
                <i class="fa fa-search"></i>
                <input type="text" id="member-search" name="search"
                       placeholder="Search for a player..." autocomplete="off" />
                <img src="{{ asset('images/loading_2.gif') }}" alt="Loading..."
                     class="desktop-search-loader" />
                <span id="searchclear" class="fa fa-times-circle"></span>
            </div>
        </div>
        <button class="help-toggle hidden-xs" title="Help Center" onclick="window.openTicketModal && window.openTicketModal()">
            <i class="fa fa-life-ring"></i>
            <span class="help-badge" style="display: none;"></span>
        </button>
        <button class="settings-toggle hidden-xs" title="Settings">
            <i class="fa fa-cog"></i>
        </button>
    </div>
</div>

<div class="mobile-search-modal">
    <div class="mobile-search-header">
        <div class="mobile-search-input-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="mobile-member-search" placeholder="Search for a player..." autocomplete="off" />
            <span class="mobile-searchclear fa fa-times-circle"></span>
        </div>
        <button class="mobile-search-close">Cancel</button>
    </div>
    <div class="mobile-search-loader">
        <div class="mobile-spinner"></div>
        <span>Searching...</span>
    </div>
    <div class="mobile-search-results"></div>
</div>