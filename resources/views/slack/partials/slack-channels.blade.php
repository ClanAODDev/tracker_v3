<table class="table table-hover adv-datatable">
    <thead>
    <tr>
        <th>Channel</th>
        <th>Created</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($channels as $channel)
        <tr>
            <td>
                #{{ $channel['name'] }}
                @if ($channel['is_archived'])
                    <div class="label label-danger">Archived</div>
                @endif
            </td>
            <td class="text-muted">

                {{ Carbon::createFromTimestamp($channel['created'])->format('Y-m-d') }}
            </td>
            <td>
                @unless ($channel['is_archived'])
                    <a href="{{ route('slack.confirm-archive-channel', $channel['id']) }}"
                       class="btn btn-danger pull-right btn-block">
                        <i class="fa fa-archive text-danger"></i> Archive
                    </a>
                @else
                    <a href="{{ route('slack.unarchive-channel', $channel['id']) }}"
                       class="btn btn-default pull-right btn-block">
                        <i class="fa fa-archive"></i> Un-archive
                    </a>
                @endunless
            </td>
        </tr>
    @endforeach
    </tbody>
</table>