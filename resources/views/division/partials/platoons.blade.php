<h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
    {{ str_plural($division->locality('platoon')) }}

    @can('create', [App\Platoon::class, $division])
        <a href="{{ route('createPlatoon', $division->abbreviation) }}"
           class="btn btn-default pull-right"><i class="fa fa-plus text-success"></i> NEW
        </a>
    @endcan
</h3>

<hr>

<div class="row">
    @forelse ($platoons as $platoon)
        <div class="col-md-6">
            <a href="{{ route('platoon', [$division->abbreviation, $platoon->id]) }}"
               class="panel panel-filled">
                <div class="panel-body">
                    <h4 class="m-b-none">
                        {{ $platoon->name }}
                        <label class="badge pull-right">{{ $platoon->members_count }} Assigned</label>
                    </h4>

                    @if ($platoon->leader)
                        <p class="list-group-item-text">
                            {{ $platoon->leader->present()->rankName }}
                        </p>
                    @else
                        <p class="list-group-item-text">TBA</p>
                    @endif

                    <p class="m-t-md">
                        <small class="slight text-muted">
                            @foreach ($platoon->squads as $squad)
                                @if ($squad->leader)
                                    <span class="badge">{{ $squad->leader->present()->rankName }}</span>
                                @else
                                    <span class="badge">TBA</span>
                                @endif
                            @endforeach
                        </small>
                    </p>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-danger text-muted">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No {{ str_plural($division->locality('platoon')) }} Found
                    </h4>
                </div>
            </div>
        </div>
    @endforelse
</div>


