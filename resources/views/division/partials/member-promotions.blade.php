@php
    $selectedYear = (int)($year ?? $promotionPeriods->first()['year'] ?? now()->year);
    $selectedMonth = (int)($month ?? $promotionPeriods->first()['month'] ?? now()->month);
    $selectedKey = sprintf('%04d-%02d', $selectedYear, $selectedMonth);
@endphp

<div class="promotions-content">
    <div class="promotions-chart-container">
        <div class="promotions-chart-header">
            <h4 class="promotions-chart-title">
                <i class="fa fa-chart-bar"></i> By Rank
            </h4>
            <form method="GET" action="{{ route('division.promotions', $division) }}" class="promotions-period-form">
                <select name="period" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach ($promotionPeriods as $p)
                        <option value="{{ $p['key'] }}" @selected($p['key'] === $selectedKey)>
                            {{ $p['label'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="promotions-chart-body">
            <canvas class="promotions-chart"
                    data-labels="{{ json_encode($ranks) }}"
                    data-values="{{ json_encode($counts) }}"
            ></canvas>
        </div>
    </div>

    <div class="promotions-main-row">
        <div class="promotions-table-container">
            <div class="promotions-table-header">
                <h4 class="promotions-table-title">
                    <i class="fa fa-medal"></i> {{ $periodLabel }}
                </h4>
                <span class="promotions-table-count">{{ $promotions->count() }} promotions</span>
            </div>
            @foreach ($promotions->groupBy(fn($p) => $p->rank?->value ?? 0)->sortKeysDesc() as $rankValue => $groupedPromotions)
                @php $rankName = $groupedPromotions->first()->rank?->getLabel() ?? 'Unknown'; @endphp
                <div class="promotions-rank-group">
                    <div class="promotions-rank-header">
                        <span class="promotions-rank-name">{{ $rankName }}</span>
                        <span class="promotions-rank-count">{{ $groupedPromotions->count() }}</span>
                    </div>
                    <table class="table table-hover promotions-table">
                        <tbody>
                        @foreach ($groupedPromotions as $action)
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
                                <td>{{ optional($action->approved_at)->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
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
