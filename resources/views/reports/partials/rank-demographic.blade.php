<div class="panel table-responsive">
    <div class="panel-heading">
        Rank Distribution
    </div>

    <table class="table table-hover">
        <thead>
        <tr>
            <th class="col-xs-4 text-center">Rank</th>
            <th class="col-xs-4 text-center">Count</th>
            <th class="no-sort col-xs-4 text-center">%</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($rankDemographic as $rank)
            <tr>
                <td class="text-center">{{ $rank->abbreviation }}</td>
                <td class="text-center">{{ number_format($rank->count) }}</td>
                <td class="text-center">
                    <div class="progress" style="margin-bottom: 0; min-width: 60px;">
                        <div class="progress-bar progress-bar-warning"
                             style="width: {{ $rank->percent }}%"></div>
                    </div>
                    <small class="slight">{{ $rank->percent }}%</small>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="active">
            <td class="text-center"><strong>Total</strong></td>
            <td class="text-center"><strong>{{ number_format($memberCount) }}</strong></td>
            <td class="text-center"><strong>100%</strong></td>
        </tr>
        </tfoot>
    </table>
</div>