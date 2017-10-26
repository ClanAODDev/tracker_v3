<h4>Ingame Clan Members</h4>
<hr />

@foreach ($data as $clan)

    <h4>{{ $clan['clan-info']->detail->name }}
        <small>{{ $clan['clan-info']->detail->motto }}</small>
    </h4>
    <div class="panel panel-filled">
        <table class="table table-hover basic-datatable">
            <thead>
            <tr>
                <th>Ingame Name</th>
                <th>Bungie Id</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($clan['clan-members'] as $member)
                <tr>
                    <td>{{ $member->destinyUserInfo->displayName }}</td>
                    <td>
                        @if (isset($member->bungieNetUserInfo->membershipId))
                            <a href="https://www.bungie.net/en/Profile/{{ $member->bungieNetUserInfo->membershipId }}">
                                <code>{{ $member->bungieNetUserInfo->membershipId }}</code>
                            </a>
                        @else
                            <span class='text-danger'>No Bungie Account</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <hr>

@endforeach