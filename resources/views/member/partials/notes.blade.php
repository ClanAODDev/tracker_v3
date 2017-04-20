<h4 class="m-t-lg">
    Member Notes
    <button type="button" class="btn btn-default pull-right"
            data-toggle="modal" data-target="#create-member-note">Add note
    </button>
</h4>
<hr />
@forelse ($notes as $note)
    <div class="panel panel-filled note {{ $note['type'] }}">
        <div class="panel-heading text-uppercase">

            @if ($note['type'] == 'sr_ldr')
                <span class="badge">SGT+</span>
            @endif

            @forelse ($note->tags as $tag)
                <small class="badge">{{ $tag->name }}</small>
            @empty
                <small class="badge text-muted">No tag</small>
            @endforelse

            <div class="pull-right">
                <span class="text-muted slight">
                    @if ($note->updated_at > $note->created_at)
                        <strong>Updated</strong>: {{ $note->updated_at->format('M d, Y') }}
                    @else
                        {{ $note->created_at->format('M d, Y') }}
                    @endif
                </span>
            </div>

        </div>

        <div class="panel-body">
            <div class="bs-example">
                {{ $note->body }}
            </div>
        </div>

        <div class="panel-footer">
            <span class="author text-muted">{{ $note->author->name }}</span>
            <div class="btn-group pull-right">
                @can('delete', $member)
                    <a href="{{ route('editNote', [$member->clan_id, $note]) }}" class="btn btn-default">
                        <i class="fa fa-wrench text-accent"></i> Edit
                    </a>
                @endcan

                @if ($note->forum_thread_id)
                    <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}" target="_blank"
                       class="btn btn-default btn-default"><i class="fa fa-comment"></i> View Discussion</a>
                @endif
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
@empty
    Member has no notes
@endforelse

{{-- add note modal form --}}
<div class="modal fade" id="create-member-note">
    <div class="modal-dialog" role="document" style="background-color: #000;">
        {!! Form::model(App\Note::class, ['method' => 'post', 'route' => ['storeNote', $member->clan_id]]) !!}
        @include('note.forms.note-form', ['action' => 'Add Member Note'])
        {!! Form::close() !!}
    </div>
</div>

@if ($errors->count())
    <script>$("#create-member-note").modal();</script>
@endif