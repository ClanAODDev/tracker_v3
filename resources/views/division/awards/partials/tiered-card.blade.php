<div class="col-lg-3 col-md-4 col-sm-6">
    <a class="award-card award-card-legendary" href="{{ route('awards.tiered', $group['slug']) }}">
        <div class="rarity-indicator rarity-legendary" title="Tiered Award"></div>
        <div class="panel-body text-center">
            <div class="award-image-wrapper">
                @if($group['topTier']->image && Storage::disk('public')->exists($group['topTier']->image))
                    <img class="clan-award" src="{{ $group['topTier']->getImagePath() }}" alt="{{ $group['topTier']->name }}" />
                @else
                    <div class="award-placeholder"><i class="fas fa-trophy"></i></div>
                @endif
            </div>
            <div class="award-card-name">
                {{ $group['name'] }}
                <i class="fa fa-layer-group text-muted" style="font-size: 11px;" title="Tiered Award"></i>
            </div>
            <div class="d-flex justify-content-center">
                <span class="award-pill pill-legendary mr-1">
                    {{ $group['tiers']->count() }} tiers
                </span>
                <span class="award-pill pill-common tiered-recipient-count">
                    {{ number_format($group['recipientCount']) }} {{ Str::plural('recipient', $group['recipientCount']) }}
                </span>
            </div>
        </div>
    </a>
</div>
