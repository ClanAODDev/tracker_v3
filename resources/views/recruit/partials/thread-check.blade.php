<hr />
<div class="thread-list">

    @foreach ($threads as $thread)
        <div class="panel panel-filled thread panel-c-{{ ($thread['status']) ? "success" : "danger" }}">
            <div class="panel-heading text-uppercase">
                {{ $thread['thread_name'] }}

                @include ('application.components.copy-button', ['data' => doForumFunction([$thread['thread_id']], 'showThread')])

                <span class="pull-right">
            @if ($thread['status'])
                        <i class="fa text-success fa-2x fa-check-circle"></i>
                    @else
                        <i class="fa text-danger fa-2x fa-times-circle"></i>
                    @endif
            </span>
            </div>
            @if ($thread['comments'])
                <div class="panel-body m-t-n">
                    {!! $thread['comments'] !!}
                </div>
            @endif
        </div>
    @endforeach
</div>
