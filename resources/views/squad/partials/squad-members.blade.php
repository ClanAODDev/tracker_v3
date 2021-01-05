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
                @include ('member.partials.member-data-row', ['squadView' => true])
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="panel-footer">
    <small class="slight badge"><span class="text-accent"><i class="fa fa-asterisk"></i></span> - On Leave</small>
    <small class="slight badge"><span style="color: magenta"><i class="fa fa-asterisk"></i></span> - Direct Recruit</small>
    <a href="{{ route('squad.export-csv', [$division, $platoon, $squad]) }}"
       class="btn btn-sm btn-accent pull-right">Export to CSV</a>
</div>
