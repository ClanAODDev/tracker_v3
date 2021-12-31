<h4 class="m-t-xl">
    Part-Time Divisions
    @can ('managePartTime', $member)
        <a href="{{ route('member.edit-part-time', $member->clan_id) }}"
           class="btn btn-default pull-right">
            <i class="fa fa-cog text-accent"></i> Manage
        </a>
    @endcan
</h4>
<hr />
<div class="row">
    @forelse ($partTimeDivisions as $division)
        <div class="col-md-4">
            <a href="{{ route('division', $division->abbreviation) }}" class="panel panel-filled">
                <div class="panel-body">
                    <h4 class="text-uppercase">
                        {{ $division->name }}
                        <span class="pull-right">
                            <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                                 class="division-icon-medium"
                                 title="{{ $division->name }}" />
                        </span>
                    </h4>
                    <span class="small text-uppercase">Since {{ $division->pivot->created_at->format('M d, Y') }}</span>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-info">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        Not part-time in any divisions
                    </h4>
                    <span class="slight">Division NCOs can add part-time members</span>
                </div>
            </div>
        </div>
    @endforelse
</div>