<h4 class="m-t-lg">Ingame Aliases</h4><hr />
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
        <div class="col-xs-12 text-muted">
            No in-game aliases
        </div>
    @endforelse
</div>