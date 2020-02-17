<div class="col-lg-3 col-md-4 col-xs-12">
    <a href="{{ $link }}" class="panel panel-filled {{ $color or null }} panel-c-success">
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