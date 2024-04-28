<h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
    {{ Str::plural($division->locality('platoon')) }}

    @can('create', [App\Models\Platoon::class, $division])
        <a href="{{ route('createPlatoon', $division->slug) }}"
           class="btn btn-default pull-right"><i class="fa fa-plus text-success"></i> NEW
        </a>
    @endcan
</h3>

<hr/>

@can('manageUnassigned', App\Models\User::class)
    @include('division.partials.unassigned-members')
@endcan

<div class="row">

    @forelse ($platoons as $platoon)
        <div class="col-md-6">
            <a href="{{ route('platoon', [$division->abbreviation, $platoon->id]) }}"
               class="panel panel-filled platoon" data-platoon-id="{{ $platoon->id }}">
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

                    <div class="m-t-lg">
                        @foreach ($platoon->squads as $squad)
                            <div class="squad label label-default m-2"
                                 style="margin-right:15px;"
                                 data-squad-id="{{ $squad->id }}"
                            >
                                {{ $squad->leader ? $squad->leader->present()->rankName : "TBA" }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-danger text-muted">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No {{ Str::plural($division->locality('platoon')) }} Found
                    </h4>
                </div>
            </div>
        </div>
    @endforelse
</div>
