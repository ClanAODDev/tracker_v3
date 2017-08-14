<div class='panel-body border-bottom'>
    <div id='playerFilter'></div>
    @include ('platoon.partials.members-table-toggle')
</div>
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
<div class="panel-footer">
    <small class="slight"><span class="text-accent"><i class="fa fa-asterisk"></i></span> - On Leave</small>
</div>
