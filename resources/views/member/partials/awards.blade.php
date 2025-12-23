@if ($member->awards->count())
    <h4 class="m-t-xl" id="achievements">Achievements</h4>
    <hr/>
    <div class="row award-grid">
        @foreach ($member->awards->sortBy('award.display_order') as $record)
            @php $rarity = $record->award->getRarity(); @endphp
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="{{ route('awards.show', $record->award) }}"
                   class="panel panel-filled member-award-card"
                   title="{{ $record->reason ?? $record->award->description }}">
                    <div class="rarity-indicator rarity-{{ $rarity }}"></div>
                    <div class="panel-body text-center">
                        <img src="{{ asset(Storage::url($record->award->image)) }}"
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