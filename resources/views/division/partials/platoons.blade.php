<h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
    {{ str_plural($division->locality('platoon')) }}

    @can('create', [App\Platoon::class, $division])
        <a href="{{ route('createPlatoon', $division->abbreviation) }}"
           class="btn btn-default pull-right"><i class="fa fa-plus text-success"></i> NEW
        </a>
    @endcan
</h3>

<hr>

<div class="row">
    @forelse ($platoons as $platoon)
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-9"></div>
                <div class="col-md-3"></div>
            </div>

        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-danger text-muted">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No {{ str_plural($division->locality('platoon')) }} Found
                    </h4>
                </div>
            </div>
        </div>
    @endforelse
</div>


