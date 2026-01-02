<div class="note-card note-{{ $note->type ?? 'general' }} {{ ($trashed ?? false) ? 'note-trashed' : '' }}">
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
                @if($trashed ?? false)
                    <span class="note-time text-danger" title="Deleted {{ $note->deleted_at->format('M j, Y g:i A') }}">
                        Deleted {{ $note->deleted_at->diffForHumans() }}
                    </span>
                    <button type="button" class="btn btn-success btn-xs restore-note-btn" data-url="{{ route('restoreNote', [$member->clan_id, $note->id]) }}">
                        <i class="fa fa-undo"></i> Restore
                    </button>
                    <button type="button" class="btn btn-danger btn-xs force-delete-note-btn" data-url="{{ route('forceDeleteNote', [$member->clan_id, $note->id]) }}">
                        <i class="fa fa-trash"></i> Delete Forever
                    </button>
                @else
                    <span class="note-time" title="{{ $note->created_at->format('M j, Y g:i A') }}">
                        {{ $note->created_at->diffForHumans() }}
                    </span>
                    @if ($note->forum_thread_id)
                        <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}"
                           target="_blank" class="btn btn-default btn-xs">
                            <i class="fas fa-external-link-alt"></i> Discussion
                        </a>
                    @endif
                    @can('delete', $note)
                        <form action="{{ route('deleteNote', [$member->clan_id, $note->id]) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this note?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>