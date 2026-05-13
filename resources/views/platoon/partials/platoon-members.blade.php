<div class='panel-body' style="padding: 10px 15px;">
    <div id='playerFilter'></div>
    @include('member.partials.tag-filter')
    @include ('member.partials.members-table-toggle')
</div>
<div class="table-responsive border-bottom">
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

<div class="panel-footer m-b-sm">
    <small class="slight"><span style="color: lightslategrey" title="On Leave"><i
                    class="fa fa-asterisk"></i></span> - On Leave
    </small>
</div>
