<a href="{{ $handle->url }}{{ $handle->pivot->value }}"
   target="_blank" class="panel panel-filled panel-c-success">
    <div class="panel-body ">
        <small class="c-white slight text-uppercase">
            {{ $handle->label }}
        </small>
        <span class="pull-right"><i class="fa fa-link"></i></span>
        <br />
        <span class="text-uppercase">{{ $handle->pivot->value }}</span>
    </div>
</a>