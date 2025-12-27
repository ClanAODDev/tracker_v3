<div class="division-header m-l-md">
    <div class="header-icon">
        <img src="{{ $logo ?? ($division ? $division->getLogoPath() : getThemedLogoPath()) }}" class="division-icon-large" />
    </div>
    <div class="header-title">
        <h3 class="m-b-xs">
            {{ $heading }}
        </h3>
        <small class="slight">
            {{ $subheading }}
        </small>
    </div>
    <hr/>
</div>
