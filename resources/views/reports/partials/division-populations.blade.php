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
            <th class="text-center col-xs-2">TeamSpeak</th>
            <th class="text-center col-xs-2">Discord</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($censuses as $division)
            <tr>
                <td>
                    <a href="{{ route('.*', $division->slug) }}">
                        <i class="fa fa-search"></i>
                    </a>
                    {{ $division->name }}
                </td>
                <td class="text-center">{{ $division->census->last()->count}}</td>
                <td class="text-center slight">
                    {{ number_format($division->weeklyTsActive / $division->total * 100, 1) }}%
                    <span class="census-pie"
                          data-colors="{{ json_encode(['#404652', '#56C0E0']) }}"
                          data-counts="{{ json_encode([$division->popMinusActive, $division->weeklyTsActive]) }}">
                    </span>
                </td>
                <td class="text-center slight">
                    {{ number_format($division->weeklyVoiceActive / $division->total * 100, 1) }}%
                    <span class="census-pie"
                          data-colors="{{ json_encode(['#404652', '#56C0E0']) }}"
                          data-counts="{{ json_encode([$division->popMinusVoiceActive, $division->weeklyVoiceActive])
                          }}">
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="panel-footer text-muted">
        <p>Populations reflect data collected during last census.</p>
    </div>
</div>

