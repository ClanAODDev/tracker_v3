<!-- Nav tabs -->
@foreach (explode(',', config('app.aod.ingame-reports.destiny-2-clans')) as $clan)
    <a href="{{ route('division.ingame-reports', $division) }}?clanId={{ $clan }}"
       class="btn btn-default">
        {{ $clan }}
    </a>
@endforeach

<hr>


<h4>{{ $data['clan-info']->detail->name }} ({{ count($data['clan-members']) }})</h4>
<hr />

<div class="panel">

    <table class="table table-hover ingame-report">
        <thead>
        <tr>
            <th>Ingame Name</th>
            <th>Bungie Id</th>
            <th>Online</th>
            <th>Last Played</th>
            <th>Type</th>
            <th>Join Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data['clan-members'] as $member)
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
                <td class="data-membershipId lastPlayed"
                    data-membership-id="{{ $member->destinyUserInfo->membershipId }}">
                    <div class="text-muted">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
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

<script>
  $(function () {

//    let table = $('.ingame-report').DataTable();

    let time = 500;
    $('.lastPlayed').each(function (index) {
      let $this = $(this);
      setTimeout(function () {
        let d2Id = $this.data('membership-id');
        let url = 'https://www.bungie.net/Platform/Destiny2/4/profile/' + d2Id + '/?components=100';
        $.ajax({
          type: 'GET',
          url: url,
          headers: {'X-API-KEY': '94ac5e6e5f0842218c17777759627876'}
        }).success(function (data) {
          if (data.Response) {
            let dateInfo = new Date(data.Response.profile.data.dateLastPlayed);
            let dateLastPlayed = dateInfo.getFullYear() + '-' + (dateInfo.getMonth() + 1) + '-' + dateInfo.getDate();
            $this.text(dateLastPlayed).effect('highlight');
          } else {
            $this.html('<span class="text-danger">ERROR</span>').effect('highlight');
          }
        });
      }, time);
      time += 300;
    });
      /*
       $('.ingame-report').DataTable({
       'paging': false,
       });*/

  });
</script>