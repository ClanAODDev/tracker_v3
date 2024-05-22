<div class="division-header">
    <div class="header-icon">
        @if ($division)
            <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                 class="division-icon-large"/>
        @else
            <img src="{{ asset('images/logo_v2.svg') }}" class="division-icon-large"/>
        @endif
    </div>
    <div class="header-title">
        <h3 class="m-b-xs">
            {{ $heading }}
        </h3>
        <small class="slight">
            {{ $subheading }}
        </small>
    </div>
    <hr />
</div>

