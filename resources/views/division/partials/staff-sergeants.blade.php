<h3 class="m-b-xs text-uppercase m-t-xxxl">Staff Sergeants</h3>
<hr />

<div class="row">
    @forelse ($staffSergeants as $staffSergeant)
        <div class="col-md-4">
            <a href="{{ route('member', $staffSergeant->getUrlParams()) }}" class="panel panel-filled panel-c-info">
                <div class="panel-body">
                    <h4 class="m-b-none">
                        {!! $staffSergeant->present()->rankName !!}
                        <span class="pull-right"><i class="pe pe-2x pe-7s-user text-muted"></i></span>
                    </h4>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-info">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No staff sergeants assigned
                    </h4>
                    <span class="slight">See clan leadership for assistance with assignments</span>
                </div>
            </div>
        </div>
    @endforelse
</div>
