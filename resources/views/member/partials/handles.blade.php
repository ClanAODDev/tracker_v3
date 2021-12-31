<h4 class="m-t-xl">
    Ingame Handles

    @can ('manageIngameHandles', $member)
        <a href="{{ route('member.edit-handles', $member->clan_id) }}" class="btn btn-default pull-right">
            <i class="fa fa-cog text-accent"></i> Manage
        </a>
    @endcan
</h4>
<hr />
<div class="row">
    @forelse ($member->handles as $handle)
        <div class="col-md-4">

            @if($handle->url)
                @include('member.partials.handle-link')
            @else
                @include('member.partials.handle')
            @endif
        </div>

    @empty
        <div class="col-xs-12">
            <div class="panel panel-filled panel-c-warning">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No ingame handles
                    </h4>
                    <span class="slight">See division NCO for assistance with ingame handles</span>
                </div>
            </div>
        </div>
    @endforelse
</div>