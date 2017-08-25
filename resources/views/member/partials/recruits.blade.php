<h4 class="m-t-xl">Recruiting History</h4>
<hr />
<table class="table adv-datatable table-striped">
    <thead>
    <tr>
        <th>Member</th>
        <th>Join Date</th>
        <th>Primary Division</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($member->recruits as $recruit)
        <tr>
            <td>
                {{ $recruit->present()->rankName }}
                <span class="pull-right">
                    <a href="{{ route('member', $recruit->clan_id) }}">
                        <i class="fa fa-search"></i>
                    </a>
                </span>
            </td>
            <td>{{ $recruit->join_date }}</td>
            <td>
                {{ $recruit->division->name or "Ex-AOD" }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>