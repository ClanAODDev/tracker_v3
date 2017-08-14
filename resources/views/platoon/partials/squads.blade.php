@forelse($squads as $squad)

    <div class="m-b-xxl">

        <div class="panel squad">
            <div class="panel-body">
                <h5 class="text-center">
                    <span class="pull-left">
                        @if($squad->leader)
                            {!! $squad->leader->present()->rankName !!}
                        @else
                            Leader TBA
                        @endif
                    </span>

                    <strong>
                        {{ $squad->name or $division->locality('squad') . " " . $loop->iteration }}
                    </strong>

                    <span class="pull-right">
                       @can('update', $squad)
                            <a href="{{ route('editSquad', [$division->abbreviation, $platoon, $squad]) }}"
                               title="Edit {{ $division->locality('squad') }}"
                               class="btn btn-default btn-sm">
                            <i class="fa fa-wrench"></i>
                        </a>
                        @endcan
                    </span>
                </h5>
            </div>

            <div class="table-responsive">

                <table class="table table-hover members-table">
                    <thead>
                    @include ('member.partials.member-header-row')
                    </thead>

                    <tbody>
                    @foreach($squad->members as $member)
                        @include('member.partials.member-data-row', ['squadView' => true])
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@empty
    <div class="panel-body text-muted">
        No {{ str_plural($division->locality('squad')) }} currently exist
    </div>
@endforelse

<div class="panel-footer">
    <span class="slight badge" title="Direct Recruit"><span
                style="color: magenta;"><i class="fa fa-asterisk"></i></span> - Recruited by the current leader
    </span>
    <span class="slight badge" title="Direct Recruit">
        <span class="text-accent"><i class="fa fa-asterisk"></i></span> - On Leave
    </span>
</div>