<div class="panel table-responsive">
    <div class="panel-heading">
        Weekly Census Data
        <span class="text-muted pull-right">{{ $previousCensus->date }}</span>
    </div>

    <table class="table table-hover basic-datatable">
        <thead>
        <tr>
            <th class="col-xs-4">Division</th>
            <th class="text-center col-xs-2">Population</th>
            <th class="text-center col-xs-2">Weekly Discord</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($censuses as $division)
            <tr>
                <td>
                    <a href="{{ route('division.census', $division->slug) }}">
                        <i class="fa fa-search"></i>
                    </a>
                    {{ $division->name }}
                </td>
                <td class="text-center">{{ $division->population }}</td>
                <td class="text-center slight">
                    {{ $division->weeklyVoicePercent }}%
                    <span class="census-pie"
                          data-colors="{{ json_encode(['#404652', '#56C0E0']) }}"
                          data-counts="{{ json_encode([$division->population - $division->weeklyVoiceActive, $division->weeklyVoiceActive]) }}">
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>

        <tfoot>
        <tr class="active">
            <td><strong>Total</strong></td>
            <td class="text-center"><strong>{{ number_format($totalPopulation) }}</strong></td>
            <td class="text-center">
                <strong>{{ $totalPopulation > 0 ? number_format($totalVoiceActive / $totalPopulation * 100, 1) : 0 }}%</strong>
                <small class="text-muted">({{ number_format($totalVoiceActive) }})</small>
            </td>
        </tr>
        </tfoot>
    </table>

    <div class="panel-footer text-muted">
        Populations reflect data collected during last census.
    </div>
</div>

