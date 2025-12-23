<div class="note-card note-{{ $note->type ?? 'general' }}">
    <div class="note-icon">
        @include('member.partials.note-icon')
    </div>
    <div class="note-content">
        <div class="note-body">
            {!! nl2br(e($note->body)) !!}
        </div>
        <div class="note-footer">
            <div class="note-author">
                @if ($note->author && $note->author->member)
                    <a href="{{ route('member', $note->author->member->getUrlParams()) }}">
                        {{ $note->author->name }}
                    </a>
                @else
                    <span class="text-muted">Unknown Author</span>
                @endif
            </div>
            <div class="note-meta">
                <span class="note-time" title="{{ $note->created_at->format('M j, Y g:i A') }}">
                    {{ $note->created_at->diffForHumans() }}
                </span>
                @if ($note->forum_thread_id)
                    <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}"
                       target="_blank" class="btn btn-default btn-xs">
                        <i class="fas fa-external-link-alt"></i> Discussion
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>