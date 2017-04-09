<h5>Aliases</h5>
@forelse ($member->handles as $handle)
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
@empty
    <div class="panel panel-filled">
        <div class="panel-body text-muted">
            None
        </div>
    </div>
@endforelse