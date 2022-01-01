<div class="col-md-3 col-xs-12" style="overflow: hidden;">
    <a href="{{ $link }}" class="panel panel-filled {{ $color ?? null }} panel-c-success">
        <div class="panel-body">
            <span class="pull-right"><i class="fa fa-link"></i></span>
            <h4 class="m-b-sm">
                {{ $data }}
            </h4>
            @if (isset($title))
                <div class="small text-uppercase">{{ $title }}</div>
            @endif
        </div>
    </a>
</div>