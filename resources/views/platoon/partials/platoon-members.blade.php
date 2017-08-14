<div class='panel-body border-bottom'>
    <div id='playerFilter'></div>
    <div class="m-t-md btn-group">
        <a class="toggle-vis btn btn-default" href="#" data-column="9">Handle</a><a class="toggle-vis btn btn-default" href="#" data-column="3">Rank</a><a class="toggle-vis btn btn-default" href="#" data-column="4">Join Date</a><a class="toggle-vis btn btn-default" data-column="5">Forum Activity</a><a class="toggle-vis btn btn-default" data-column="6">TS Activity</a>
    </div>
</div>
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
<div class="panel-footer">
    <small class="slight"><span class="text-accent"><i class="fa fa-asterisk"></i></span> - On Leave</small>
</div>
