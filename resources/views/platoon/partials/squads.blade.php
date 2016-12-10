@forelse($squads as $squad)
    <div class="col-md-6">
        <div class="panel panel-primary squad">
            <div class="panel-heading">
                Squad #{{ $loop->index + 1 }}
                <span class="badge pull-right">{{ $squad->members->count() }}</span>
            </div>

            <div class="panel-body" style="height: 250px; max-height: 250px; overflow-y: scroll;">

                @forelse($squad->members->chunk(ceil($squad->members->count() / 2)) as $chunk)
                    <div class="col-md-6 list-group">
                        @foreach($chunk as $member)
                            <a href="{{ route('member', $member->clan_id) }}"
                               class="list-group-item wrap-ellipsis">
                                {!! $member->present()->rankName !!}
                            </a>
                        @endforeach
                    </div>
                @empty
                    <div class="text-muted">No members assigned</div>
                @endforelse
            </div>

        </div>
    </div>

@empty
    <div class="col-xs-8">
        <div class="panel panel-primary squad">
            <div class="panel-heading">Squads</div>
            <div class="panel-body">
                <p>There are no squads</p>
            </div>
        </div>
    </div>
@endforelse