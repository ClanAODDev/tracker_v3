<div class="panel">
    <div class="panel-body">

        <div class="row">
            <div class="col-xs-9">

                <h1 class="m-t-md m-b-xs">
                    <i class="pe pe-7s-add-user text-warning"> </i>
                    {{ number_format($memberCount-$recruitCount) }}
                </h1>

                <div class="small">
                    <span class="c-white">Number of Recruits</span> in the Angels of Death clan across all divisions.
                </div>
            </div>
            <div class="col-xs-3">
                <div class="m-t-md"
                     data-count="{{ json_encode([$recruitCount, $memberCount-$recruitCount]) }}"
                     recruit-count></div>
            </div>
        </div>
    </div>
</div>
