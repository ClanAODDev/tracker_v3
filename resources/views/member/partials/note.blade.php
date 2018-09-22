<div class="vertical-timeline-block note">

    <div class="vertical-timeline-icon">
        @include('member.partials.note-icon')
    </div>

    <div class="vertical-timeline-content panel">
        <div class="p-sm">
            <div class="panel-body">
                <p style="margin: 0;">{!! nl2br($note->body) !!}
                </p>
            </div>

            <div class="panel-footer">
                <div class="slight">

                    <a href="{{ route('member', $note->author->member->getUrlParams()) }}">{{ $note->author->name }}</a>
                    &mdash;
                    {{ $note->created_at->diffForHumans() }}

                    @if ($note->forum_thread_id)
                        <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}"
                           target="_blank" class="btn btn-default btn-xs m-l-sm">View Discussion</a>
                    @endif

                    @if ($member->division)
                        @can('edit', [$note, $member->clan_id])
                            <a href="{{ route('editNote', [$member->clan_id, $note]) }}"
                               class="btn btn-default btn-xs">Edit</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>