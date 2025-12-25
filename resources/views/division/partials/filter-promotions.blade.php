@php
    $selectedYear = (int)($year ?? $promotionPeriods->first()['year'] ?? now()->year);
    $selectedMonth = (int)($month ?? $promotionPeriods->first()['month'] ?? now()->month);
    $selectedKey = sprintf('%04d-%02d', $selectedYear, $selectedMonth);
@endphp

<div class="report-filter">
    <form method="GET" action="{{ route('division.promotions', $division) }}" class="report-filter-form">
        <div class="report-filter-group">
            <label for="period">Period</label>
            <select name="period" id="period" class="form-control" onchange="this.form.submit()">
                @foreach ($promotionPeriods as $p)
                    <option value="{{ $p['key'] }}" @selected($p['key'] === $selectedKey)>
                        {{ $p['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <noscript>
            <button type="submit" class="btn btn-accent">
                <i class="fa fa-filter"></i> Apply
            </button>
        </noscript>
    </form>
</div>
