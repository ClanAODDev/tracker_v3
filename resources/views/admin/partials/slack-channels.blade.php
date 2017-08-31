<table class="table table-hover">
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
            </td>
            <td class="text-muted">
                {{ Carbon::instance($channel->getCreated())->diffForHumans() }}
            </td>
            <td>
                @unless ($channel->isArchived())
                    <a href="{{ route('slack.confirm-archive-channel', $channel->getId()) }}"
                       class="btn btn-danger pull-right center-block">
                        <i class="fa fa-archive text-danger"></i> Archive
                    </a>
                @else
                    <button class="btn btn-default pull-right" disabled>
                        <i class="fa fa-archive text-muted"></i> Archived
                    </button>
                @endunless
            </td>
        </tr>
    @endforeach
    </tbody>
</table>