<div class="panel panel-filled">
    <div class="panel-body">
        <small class="c-white slight text-uppercase">
            {{ $handle->label }}
            <button data-clipboard-text="{{ $handle->pivot->value }}"
                    class="copy-to-clipboard btn-outline-warning btm-xs btn" style="float: right;display: inline;">
                <i class="far fa-copy" title="Copy to clipboard"></i>
            </button>
        </small>
        <br />
        <span class="text-uppercase">{{ $handle->pivot->value }}</span>
    </div>
</div>