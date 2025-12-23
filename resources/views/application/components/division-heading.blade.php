<div class="division-header">
    <div class="header-icon">
        <img src="{{ $division ? $division->getLogoPath() : asset(config('aod.logo')) }}" class="division-icon-large" />
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

