<div class="col-lg-3 col-md-4 col-sm-6">
    <a class="panel panel-filled award-card" href="{{ route('awards.show', $award) }}">
        <div class="rarity-indicator rarity-{{ $award->rarity }}" title="{{ ucfirst($award->rarity) }}"></div>
        <div class="panel-body text-center">
            <img src="{{ asset(Storage::url($award->image)) }}"
                 class="clan-award-zoom clan-award"
                 alt="{{ $award->name }}"
                 loading="lazy"
                 style="margin-bottom: 10px;"
            />
            <div class="award-card-name">
                {{ $award->division ? Str::replace($award->division->name . ' - ', '', $award->name) : $award->name }}
                @if ($award->allow_request)
                    <i class="fa fa-hand-paper-o text-success" title="Requestable"></i>
                @endif
            </div>
            <span class="award-pill pill-{{ $award->rarity }}">
                {{ $award->recipients_count }} {{ Str::plural('recipient', $award->recipients_count) }}
            </span>
        </div>
    </a>
</div>
