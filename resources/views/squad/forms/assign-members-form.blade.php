<h4>Assign members</h4>
<hr />
<div class="row">
    <div class="col-xs-6">
        <div class="panel panel-filled">
            <div class="panel-heading">Unassigned Members
                <span class="unassigned-count badge pull-right">{{ $platoon->unassigned->count() }}</span>
                <div class="form-group m-b-none m-t-lg">
                    <input type="text" id="search-collection" class="form-control" placeholder="Filter members..." />
                </div>
            </div>
            <div class="panel-body unassigned-members collection" id="selectable"
                 style="max-height: 300px; overflow-y: scroll;">
                <div class="form-group">
                    <input type="text" id="search-collection" class="form-control" placeholder="Filter members..." />
                </div>
                @forelse ($platoon->unassigned as $member)
                    <li class="list-group-item collection-item"
                        data-member-id="{{ $member->id }}">{{ $member->name }}
                        <small class="text-muted">{{ $member->rank->abbreviation }}</small>
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