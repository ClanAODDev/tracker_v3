<div class="col-xl-2 col-md-4 col-sm-6">
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
                {{ $award->name }}
                @if ($award->allow_request)
                    <i class="fa fa-hand-paper-o text-success" title="Requestable"></i>
                @endif
            </div>
            <div class="award-card-count text-{{ $award->rarity }}">
                {{ $award->recipients_count }} {{ Str::plural('recipient', $award->recipients_count) }}
            </div>
        </div>
    </a>
</div>
