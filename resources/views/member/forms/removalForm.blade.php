@can('delete', $member)
    <div class="panel panel-danger">
        <div class="panel-heading"><i class="fa fa-trash fa-lg"></i> Remove from AOD</div>
        <div class="panel-body">
            <p>This action will take you to the AOD Mod CP and process the member for removal. Once you confirm, the action is non-reversible.</p>

            <form action="{{ action('MemberController@destroy', $member->clan_id) }}" method="post">
                {{ method_field('DELETE') }}
                <div class="form-group">
                    <textarea class="form-control" name="removal-reason" placeholder="Reason"
                              id="removal-reason" required></textarea>
                </div>

                <button type="submit" title="Remove player from AOD"
                        class="btn btn-danger">Submit<span class="hidden-sm hidden-xs"> removal</span></button>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

            </form>
        </div>
    </div>
@else
    {{-- else show request removal--}}

    <div class="panel panel-danger">
        <div class="panel-heading"><i class="fa fa-trash fa-lg"></i> Request Removal from AOD</div>
        <div class="panel-body">
            <p>By submitting this form, you are requesting that this member be permanently removed from AOD. Once this request has been approved, the action cannot be reversed.</p>

            <form>
                <div class="form-group">
                    <textarea class="form-control" name="removal-reason" placeholder="Reason"
                              id="removal-reason" required></textarea>
                </div>

                <button type="submit" title="Remove player from AOD"
                        class="btn btn-danger">Submit<span class="hidden-sm hidden-xs"> request</span></button>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </div>
    </div>
@endcan