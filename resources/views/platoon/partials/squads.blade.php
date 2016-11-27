<div class="row margin-top-50">
    @foreach($squads as $squad)
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Squad #{{ $loop->index + 1 }}
                </div>

                <div style="max-height: 300px; overflow-y: scroll;">
                    <table class="table table-striped table-hover">
                    @forelse($squad->members as $member)
                            <tr>
                                <td>{!! $member->present()->nameWithIcon !!}</td>
                                <td>{{ $member->last_forum_login->diffForHumans() }}</td>
                            </tr>
                    @empty
                        <li class="text-muted list-group-item">No members assigned</li>
                    @endforelse
                    </table>
                </div>

            </div>
        </div>
    @endforeach
</div>