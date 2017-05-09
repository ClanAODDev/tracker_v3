<div class="vertical-timeline-block note">

    <div class="vertical-timeline-icon">
        @if($note->type == 'negative')
            <i class="fa fa-thumbs-down text-danger"></i>
        @elseif ($note->type == 'positive')
            <i class="fa fa-thumbs-up text-success"></i>
        @elseif ($note->type == 'sr_ldr')
            <i class="fa fa-shield text-primary"></i>
        @else
            <i class="fa fa-comment text-accent"></i>
        @endif
    </div>

    <div class="vertical-timeline-content">
        <div class="p-sm">
                    <span class="vertical-date pull-right text-muted"> <small>
                            @if ($note->updated_at > $note->created_at)
                                <i class="fa fa-pencil text-muted" title="Edited"></i>
                                {{ $note->updated_at->format('d M Y') }}
                            @else
                                {{ $note->created_at->format('d M Y') }}
                            @endif
                        </small>
                    </span>

            @if ($note['type'] == 'sr_ldr')
                <span class="label label-default slight">SGT+</span>
            @endif

            @forelse ($note->tags as $tag)
                <span class="label label-default slight text-uppercase">{{ $tag->name }}</span>
            @empty
                <span class="label label-default slight text-uppercase">NO TAG</span>
            @endforelse

            <p class="bs-example">{{ $note->body }} </p>

            <div class="m-t-md">
                <small class="text-muted">Posted by {{ $note->author->name }}</small>

                <div class="pull-right text-muted">

                    @if ($note->forum_thread_id)
                        <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}"
                           target="_blank" class="btn btn-default btn-xs m-l-sm">View Discussion</a>
                    @endif

                    @can('edit', [$note, $member->clan_id])
                        <a href="{{ route('editNote', [$member->clan_id, $note]) }}"
                           class="btn btn-default btn-xs">Edit</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>