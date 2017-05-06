<div class="vertical-container v-timeline" style="margin-top: 0;">
    @forelse ($notes as $note)
        <div class="vertical-timeline-block note">
            <div class="vertical-timeline-icon">
                @if($note->type == 'negative')
                    <i class="fa fa-thumbs-down text-danger"></i>
                @elseif ($note->type == 'positive')
                    <i class="fa fa-thumbs-up text-success"></i>
                @elseif ($note->type == 'sr_ldr')
                    <i class="fa fa-shield text-danger"></i>
                @else
                    <i class="fa fa-comment text-accent"></i>
                @endif
            </div>
            <div class="vertical-timeline-content">
                <div class="p-sm">
                    <span class="vertical-date pull-right text-muted"> <small>
                            @if ($note->updated_at > $note->created_at)
                                {{ $note->updated_at->format('d M Y') }}
                                <i class="fa fa-pencil text-muted" title="Edited"></i>
                            @else
                                {{ $note->created_at->format('d M Y') }}
                            @endif
                        </small>
                    </span>

                    @if ($note['type'] == 'sr_ldr')
                        <span class="label label-default slight">SGT+</span>
                    @endif

                    @foreach ($note->tags as $tag)
                        <span class="label label-default slight text-uppercase">{{ $tag->name }}</span>
                    @endforeach

                    <p class="bs-example">{{ $note->body }} </p>

                    <div class="m-t-md">
                        <small class="text-muted">Posted by {{ $note->author->name }}</small>
                        @can('edit', [$note, $member->clan_id])
                            <a href="{{ route('editNote', [$member->clan_id, $note]) }}"
                               class="pull-right btn btn-default btn-xs">Edit</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @empty

        <p>None</p>

    @endforelse
</div>

<div class="modal fade" id="create-member-note">
    <div class="modal-dialog" role="document" style="background-color: #000;">
        {!! Form::model(App\Note::class, ['method' => 'post', 'route' => ['storeNote', $member->clan_id]]) !!}
        @include('member.forms.note-form', ['action' => 'Add Member Note'])
        {!! Form::close() !!}
    </div>
</div>

@if ($errors->count())
    <script>$("#create-member-note").modal();</script>
@endif
