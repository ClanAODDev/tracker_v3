<div class="col-md-4 col-xs-12">
    <div class="panel panel-filled {{ $color ?? null }}">
        <div class="panel-body">
            <h4 class="m-b-sm {{ (!isset($isUppercase) || $isUppercase) ? "text-uppercase" : null }}">
                {{ $data }}
            </h4>
            <div class="small text-uppercase">{{ $title }}</div>
        </div>
    </div>
</div>