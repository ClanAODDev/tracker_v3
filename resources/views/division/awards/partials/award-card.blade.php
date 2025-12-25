<div class="{{ !empty($small) ? 'col-lg-2 col-md-3 col-sm-4' : 'col-lg-3 col-md-4 col-sm-6' }}">
    <a class="award-card award-card-{{ $award->rarity }}" href="{{ route('awards.show', $award) }}">
        <div class="rarity-indicator rarity-{{ $award->rarity }}" title="{{ ucfirst($award->rarity) }}"></div>
        <div class="panel-body text-center">
            <div class="award-image-wrapper">
                @if($award->image && Storage::disk('public')->exists($award->image))
                    <img src="{{ $award->getImagePath() }}"
                         class="clan-award-zoom clan-award"
                         alt="{{ $award->name }}"
                         loading="lazy"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                    />
                    <div class="award-placeholder" style="display:none">
                        <i class="fas fa-trophy"></i>
                    </div>
                @else
                    <div class="award-placeholder">
                        <i class="fas fa-trophy"></i>
                    </div>
                @endif
            </div>
            <div class="award-card-name">
                {{ $award->division ? Str::replace($award->division->name . ' - ', '', $award->name) : $award->name }}
                @if ($award->allow_request && empty($legacy))
                    <i class="fa fa-hand-pointer text-success" title="Requestable"></i>
                @endif
            </div>
            <span class="award-pill pill-{{ $award->rarity }}">
                {{ $award->recipients_count }} {{ Str::plural('recipient', $award->recipients_count) }}
            </span>
        </div>
    </a>
</div>
