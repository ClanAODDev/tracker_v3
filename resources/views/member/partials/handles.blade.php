<h4 class="m-t-xl">
    Ingame Handles

    @can ('update', $member)
        <a href="{{ route('editMember', $member->clan_id) . '#handles' }}" class="btn btn-default pull-right btn-sm">
            <i class="fa fa-cog text-accent"></i> Manage
        </a>
    @endcan
</h4>
<hr />
<div class="row">
    @forelse ($member->handles as $handle)
        <div class="col-md-4">
            <div class="panel panel-filled panel-c-accent">
                <div class="panel-body">
                    <small class="c-white slight text-uppercase">
                        {{ $handle->label }}
                    </small>
                    <br />
                    <span class="text-uppercase">
                        {{ $handle->pivot->value }}
                    </span>
                </div>
            </div>
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