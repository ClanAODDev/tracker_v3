<div class='panel-body border-bottom'>
    <div id='playerFilter'></div>
    @include ('member.partials.members-table-toggle')
    <div class="table-responsive">

        <table class='table table-hover members-table'>
            <thead>
            @include ('member.partials.member-header-row')
            </thead>
            <tbody>
            @foreach ($members as $member)
                @include ('member.partials.member-data-row')
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="panel-footer m-b-sm">
    <small class="slight"><span style="color: lightslategrey" title="On Leave"><i
                    class="fa fa-asterisk"></i></span> - On Leave
    </small>
    <a href="{{ route('platoon.export-csv', [$platoon->division, $platoon]) }}"
              class="btn btn-sm btn-accent pull-right">Export to CSV</a>
</div>
