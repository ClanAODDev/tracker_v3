@if ($member->awards->count())
    <div class="achievements-header m-t-xl" id="achievements">
        <h4>
            Achievements
            <span class="badge">{{ $memberStats->awards->total }}</span>
        </h4>
        <div class="rarity-summary">
            @foreach(['mythic', 'legendary', 'epic', 'rare', 'common'] as $rarity)
                @if($memberStats->awards->byRarity->get($rarity, 0) > 0)
                    <span class="award-pill pill-{{ $rarity }}">
                        {{ $memberStats->awards->byRarity->get($rarity) }} {{ ucfirst($rarity) }}
                    </span>
                @endif
            @endforeach
        </div>
    </div>
    <hr/>
    <div class="row award-grid">
        @foreach ($member->awards->sortBy('award.display_order') as $record)
            @php $rarity = $record->award->getRarity(); @endphp
            <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                <a href="{{ route('awards.show', $record->award) }}"
                   class="panel panel-filled member-award-card member-award-card-{{ $rarity }}"
                   title="{{ $record->reason ?? $record->award->description }}">
                    <div class="rarity-indicator rarity-{{ $rarity }}"></div>
                    <div class="panel-body text-center">
                        <img src="{{ $record->award->getImagePath() }}"
                             alt="{{ $record->award->name }}"
                             class="clan-award" loading="lazy"
                             style="margin-bottom: 8px;"
                        />
                        <div class="award-card-name">{{ $record->award->name }}</div>
                        <span class="award-pill pill-{{ $rarity }}">{{ $record->created_at->format('M d, Y') }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif