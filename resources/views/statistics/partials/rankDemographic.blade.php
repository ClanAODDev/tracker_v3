<div class="panel panel-filled table-responsive">
    <table class="table table-hover">
        <thead>
        <th class="text-center">Rank</th>
        <th class="text-center">Count</th>
        </thead>
        <tbody>
        @foreach ($rankDemographic as $rank)
            <tr>
                <td class="text-center">{{ $rank->abbreviation }}</td>
                <td class="text-center">{{ $rank->count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>