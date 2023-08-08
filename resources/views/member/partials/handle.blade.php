<div class="panel panel-filled">
    <div class="panel-body">
        <small class="c-white slight text-uppercase">
            {{ $handle->label }}
            <button data-clipboard-text="{{ $handle->pivot->value }}"
                    class="copy-to-clipboard btn-outline-warning btm-xs btn" style="float: right;display: inline;">
                <i class="fa fa-clone"></i>
            </button>
        </small>
        <br />
        <span class="text-uppercase">{{ $handle->pivot->value }}</span>
    </div>
</div>