<div class="row">
    <div class="col-md-9">
        <div class="panel panel-filled">
            <div class="panel-body">
                @foreach ($promotions->groupBy(fn ($a) => $a->rank?->name ?? 'Unspecified') as $rankName => $group)
                    <div class="panel m-b-none">
                        <div class="panel-body">
                            <table class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>{{ $rankName }}</th>
                                    <th class="text-right text-muted slight">Approved At</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($group as $action)
                                    <tr>
                                        <td>{{ $action->member?->name }}</td>
                                        <td class="text-right text-muted slight">
                                            {{ optional($action->approved_at)->toDateTimeString() }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="text-accent">Total</td>
                                    <td class="text-accent text-right">{{ $group->count() }}</td>
                                </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-filled">
            <div class="panel-body">
                <canvas class="promotions-chart"
                        data-labels="{{ json_encode($ranks) }}"
                        data-values="{{ json_encode($counts) }}"
                ></canvas>
            </div>
        </div>

        <div class="panel panel-filled">
            <div class="panel-heading">Share Promotions</div>
            <div class="panel-body">

                <pre id="bb-code-promos">@include('division.partials.promo-bb-code')</pre>

                <button data-clipboard-target="#bb-code-promos" class="copy-to-clipboard btn-success btn">
                    <i class="fa fa-clone"></i> Copy BB-Code
                </button>
            </div>
        </div>
    </div>
</div>
