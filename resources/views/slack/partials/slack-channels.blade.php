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
                #{{ $channel->getName() }}
                @if ($channel->isArchived())
                    <div class="label label-danger">Archived</div>
                @endif
            </td>
            <td class="text-muted">
                {{ Carbon::instance($channel->getCreated())->format('Y-m-d') }}
            </td>
            <td>
                @unless ($channel->isArchived())
                    <a href="{{ route('slack.confirm-archive-channel', $channel->getId()) }}"
                       class="btn btn-danger pull-right btn-block">
                        <i class="fa fa-archive text-danger"></i> Archive
                    </a>
                @else
                    <a href="{{ route('slack.unarchive-channel', $channel->getId()) }}"
                       class="btn btn-default pull-right btn-block">
                        <i class="fa fa-archive"></i> Un-archive
                    </a>
                @endunless
            </td>
        </tr>
    @endforeach
    </tbody>
</table>