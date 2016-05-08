{{-- if user sgt or higher, show remove button --}}
@if ($member->rank_id >= 9)
    <a href="#" title="Remove player from AOD" class="removeMember btn btn-danger"><i
                class="fa fa-trash fa-lg"></i> Remove<span class="hidden-sm hidden-xs"> from AOD</span></a>

@else

    {{-- else show request removal--}}
    <a href="#" title="Remove player from AOD" class="requestRemoval btn btn-warning"><i
                class="fa fa-trash fa-lg"></i> Request<span class="hidden-sm hidden-xs"> removal</span></a>
@endif