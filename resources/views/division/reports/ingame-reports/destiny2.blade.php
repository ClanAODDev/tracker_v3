<h4>Ingame Clan Members</h4>
<hr />

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    @foreach ($data as $clan)
        <li role="presentation" class="{{ $loop->iteration == 1 ? 'active' : null }}">
            <a href="#clan-{{ $clan['clan-info']->detail->groupId }}" aria-controls="home" role="tab"
               data-toggle="tab">{{ $clan['clan-info']->detail->motto }} ({{ count($clan['clan-members']) }})</a>
        </li>
    @endforeach
</ul>

<!-- Tab panes -->
<div class="tab-content">
    @foreach ($data as $clan)
        <div role="tabpanel" class="tab-pane {{ $loop->iteration == 1 ? 'active' : null }}"
             id="clan-{{ $clan['clan-info']->detail->groupId }}">
            <div class="panel">
                <div class="panel-body">
                    <table class="table table-hover adv-datatable">
                        <thead>
                        <tr>
                            <th>Ingame Name</th>
                            <th>Bungie Id</th>
                            <th>Online</th>
                            <th>Type</th>
                            <th>Join Date</th>
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
                                <td>
                                    @if ($member->isOnline)
                                        <i class="fa fa-check text-success"></i>
                                    @endif
                                </td>
                                <td><code>{{ $member->memberType }}</code> {{ bungieMemberType($member->memberType) }}
                                </td>
                                <td title="{{ Carbon::parse($member->joinDate)->diffForHumans() }}">
                                    {{ Carbon::parse($member->joinDate)->format('Y-m-d') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>