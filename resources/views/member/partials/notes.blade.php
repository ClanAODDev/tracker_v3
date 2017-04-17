@forelse ($notes as $note)
    <div class="panel panel-filled note {{ $note['type'] }} collapsed">
        <div class="panel-heading text-uppercase">

            @if ($note['type'] == 'admin')
                <span class="badge">{{ $note['type'] }}</span>
            @endif

            <span class="badge">COC</span>
                
            <div class="panel-tools">
                <span class="text-muted slight">{{ $note->updated_at->format('M d, Y') }}</span>
                <a class="panel-toggle"><i class="fa fa-chevron-down"></i></a>
            </div>
        </div>

        <div class="panel-body">
            {{ $note->body }}
        </div>

        <div class="panel-footer">
            <span class="author text-muted">{{ $note->author->name }}</span>
            @if ($note->forum_thread_id)
                <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}" target="_blank"
                   class="btn btn-default btn-xs pull-right"><i class="fa fa-comment"></i> View Discussion</a>
            @endif
        </div>
    </div>
@empty
    <div class="panel panel-filled">
        <div class="panel-body text-muted">
            Member has no notes
        </div>
    </div>
@endforelse

{!! Form::open(['method' => 'post', 'route' => ['storeNote', $member->clan_id]]) !!}
@include('member.forms.add-note-form')
{!! Form::close() !!}