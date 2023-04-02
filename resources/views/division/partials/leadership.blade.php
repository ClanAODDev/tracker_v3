<h3 class="m-b-xs text-uppercase m-t-xxxl">Leadership</h3>
<hr />

<div class="row">
    @forelse($divisionLeaders as $leader)
        <div class="col-md-4">
            <a href="{{ route('member', $leader->getUrlParams()) }}" class="panel panel-filled panel-c-danger">
                <div class="panel-body">
                    <h4 class="m-b-none">
                        {!! $leader->present()->rankName !!}
                        <span class="pull-right"><i class="pe pe-2x pe-7s-shield text-muted"></i></span>
                    </h4>
                    <small>{{ $leader->position->name()() ?? '' }}</small>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-accent">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No leadership assigned
                    </h4>
                    <span class="slight">See clan leadership for assistance with assignments</span>
                </div>
            </div>
        </div>
    @endforelse
</div>