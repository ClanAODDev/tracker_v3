<table class="table table-hover">
    <thead>
    <tr>
        <th>Channel</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($channels as $channel)
        <tr>
            <td>
                #{{ $channel->getName() }}
            </td>
            <td>
                <a href="{{ route('slack.confirm-archive-channel', $channel->getId()) }}" class="btn btn-danger pull-right">
                    <i class="fa fa-archive text-danger"></i> Archive Channel
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>