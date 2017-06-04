<h4 class="m-t-lg">
    Ingame Handles
    <a href="{{ route('editMember', $member->clan_id) . '#handles' }}" class="btn btn-default pull-right btn-sm">
        <i class="fa fa-cog text-accent"></i> Manage
    </a>
</h4>
<hr />
<div class="row">
    @forelse ($member->handles as $handle)
        <div class="col-md-4">
            <div class="panel panel-filled">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6">
                            {{ $handle->pivot->value }}
                        </div>
                        <div class="col-xs-6 m-t-xs">
                            <small class="text-muted slight pull-right text-right">
                                {{ $handle->name }}
                            </small>
                        </div>
                    </div>
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