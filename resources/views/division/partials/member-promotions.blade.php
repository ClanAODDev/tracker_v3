<div class="promotions-content">
    <div class="promotions-main">
        <div class="promotions-table-container">
            <div class="promotions-table-header">
                <h4 class="promotions-table-title">
                    <i class="fa fa-medal"></i> {{ $periodLabel }}
                </h4>
                <span class="promotions-table-count">{{ $promotions->count() }} promotions</span>
            </div>
            <table class="table table-hover promotions-table">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>Rank</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($promotions as $action)
                    <tr>
                        <td>
                            @if($action->member)
                                <a href="{{ route('member', $action->member->getUrlParams()) }}">
                                    {{ $action->member->name }}
                                </a>
                            @else
                                Unknown
                            @endif
                        </td>
                        <td>{{ $action->rank?->name ?? '—' }}</td>
                        <td>{{ optional($action->approved_at)->format('M j, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="promotions-sidebar">
        <div class="promotions-chart-container">
            <div class="promotions-chart-header">
                <h4 class="promotions-chart-title">
                    <i class="fa fa-chart-pie"></i> By Rank
                </h4>
            </div>
            <div class="promotions-chart-body">
                <canvas class="promotions-chart"
                        data-labels="{{ json_encode($ranks) }}"
                        data-values="{{ json_encode($counts) }}"
                ></canvas>
            </div>
        </div>

        <div class="promotions-share-container">
            <div class="promotions-share-header">
                <h4 class="promotions-share-title">
                    <i class="fa fa-share-alt"></i> Share Promotions
                </h4>
            </div>
            <div class="promotions-share-body">
                <pre id="bb-code-promos">@include('division.partials.promo-bb-code')</pre>
                <button data-clipboard-target="#bb-code-promos" class="copy-to-clipboard btn btn-accent btn-block">
                    <i class="fa fa-clone"></i> Copy BB-Code
                </button>
            </div>
        </div>
    </div>
</div>
