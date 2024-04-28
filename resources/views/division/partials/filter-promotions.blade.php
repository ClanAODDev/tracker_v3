<div class="panel panel-filled panel-c-info">
    <div class="panel-body">
        <label for="promotion-periods">Filter by promotion period</label>
        <select name="promotion-periods" id="promotion-periods" class="form-control"
                onChange="top.location.href=this.options[this.selectedIndex].value;">
            <option value="" disabled selected>Select a period</option>
            @foreach ($promotionPeriods as $period)
                <option value="{{ route('division.promotions', [$division->abbreviation, $period->month, $period->year]) }}">
                    {{ $period->month }} {{ $period->year }}
                </option>
            @endforeach
        </select>
    </div>
</div>