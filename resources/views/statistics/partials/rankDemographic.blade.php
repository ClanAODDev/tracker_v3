<div class="panel panel-filled table-responsive">
    <table class="table table-hover">
        <thead>
        <th class="col-xs-4 text-center">Rank</th>
        <th class="col-xs-4 text-center">Count</th>
        <th class="no-sort col-xs-4"></th>
        </thead>
        <tbody>
        @foreach ($rankDemographic as $rank)
            <tr>
                <td class="text-center">{{ $rank->abbreviation }}</td>
                <td class="text-center">{{ $rank->count }}</td>
                <td class="text-center">
                    <span class="census-pie"
                          data-counts="{{ json_encode([$rank->difference, $rank->count]) }}"></span>
                    <small class="slight">{{ round($rank->count / $memberCount * 100, 1) }}%</small>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>