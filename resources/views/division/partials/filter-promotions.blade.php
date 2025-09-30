<form method="GET" action="{{ route('division.promotions', $division) }}" class="form-inline m-b">
    <div class="form-group">
        <label for="period" class="m-r-sm">Period</label>
        <select name="period" id="period" class="form-control">
            @foreach ($promotionPeriods as $p)
                <option value="{{ $p['year'] }}-{{ str_pad($p['month'], 2, '0', STR_PAD_LEFT) }}"
                        @selected(
                            (request('year') == $p['year'] && request('month') == $p['month'])
                            || ((int)($year ?? 0) === $p['year'] && (int)($month ?? 0) === $p['month'])
                        )>
                    {{ $p['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <input type="hidden" name="year"  id="yearField"  value="{{ (int)($year ?? 0) }}">
    <input type="hidden" name="month" id="monthField" value="{{ (int)($month ?? 0) }}">

    <button type="submit" class="btn btn-primary m-l-sm">Apply</button>
</form>

<script>
    document.getElementById('period')?.addEventListener('change', function () {
        const [yr, mo] = this.value.split('-');
        document.getElementById('yearField').value  = parseInt(yr, 10);
        document.getElementById('monthField').value = parseInt(mo, 10);
    });
</script>
