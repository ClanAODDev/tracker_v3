<h4>Assign members</h4>
<hr />
<p>If there are unassigned members in the division, you can assign them to this platoon by selecting them. Bear in mind that you will still need to create squads, and assign members to those as well.</p>

<div class="row">
    <div class="col-xs-6">
        <div class="panel panel-filled">
            <div class="panel-heading">Unassigned Members
                <span class="unassigned-count badge pull-right">{{ $platoon->unassigned->count() }}</span>
            </div>
            <div class="panel-body unassigned-members" id="selectable" style="max-height: 300px; overflow-y: scroll;">
                @forelse ($platoon->unassigned as $member)
                    <li style="cursor: pointer;" class="list-group-item clearfix" data-member-id="{{ $member->id }}">
                        {{ $member->present()->rankName }}
                    </li>
                @empty
                    <p class="text-muted">No unassigned members available</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <div class="panel panel-filled">
            <div class="panel-heading">Selected Members
                <span class="assigned-count badge pull-right">0</span>
            </div>
            <div class="panel-body assigned-members" style="max-height: 300px; overflow-y: scroll;"></div>
        </div>
    </div>
</div>

<input type="hidden" id="assigned-member-ids" name="member_ids" />

<script>
    $(".unassigned-members li").click(function () {
        assignMember($(this), $('.assigned-members'));
        updateMembers();
    });

    $(".assigned-members").on("click", "li", function () {
        assignMember($(this), $('.unassigned-members'));
        updateMembers();
    });

    /**
     * Parse assigned members
     */
    function updateMembers() {
        let ids = [],
            assigned = $('.assigned-members li'),
            unassigned = $('.unassigned-members li');

        assigned.each(function () {
            ids.push($(this).data('member-id'));
        });

        $(".assigned-count").text(assigned.length);
        $(".unassigned-count").text(unassigned.length);

        $("#assigned-member-ids").val(JSON.stringify(ids));
    }

    function assignMember(member, list) {
        let item = member.clone();

        $(list).append(item);
        item.effect('highlight');
        member.remove();

        $(".assigned-members").stop().animate({
            scrollTop: $('.assigned-members').prop("scrollHeight")
        }, 1000);
    }
</script>