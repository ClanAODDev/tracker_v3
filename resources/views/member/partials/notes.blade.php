@forelse ($notes as $note)
    <div class="panel panel-filled panel-toggle note {{ $note['type'] }} collapsed">
        <div class="panel-heading text-uppercase">

            @if ($note['type'] == 'admin')
                <span class="badge">{{ $note['type'] }}</span>
            @endif

            <span class="badge">COC</span>

            <div class="panel-tools">
                <span class="text-muted slight">{{ $note->updated_at->format('M d, Y') }}</span>
                <i class="fa fa-chevron-up text-muted"></i>
            </div>
        </div>

        <div class="panel-body">
            <div class="bs-example">
                {{ $note->body }}
            </div>
        </div>

        <div class="panel-footer">
            <span class="author text-muted slight">{{ $note->author->name }}</span>
            <div class="btn-group pull-right">
                <a href="#" class="btn btn-default"><i class="fa fa-wrench text-accent"></i> Edit</a>
                <a href="#" class="btn btn-default"><i class="fa fa-trash text-danger"></i> Delete</a>
                @if ($note->forum_thread_id)
                    <a href="{{ doForumFunction([$note->forum_thread_id], 'showThread') }}" target="_blank"
                       class="btn btn-default btn-default"><i class="fa fa-comment"></i> View Discussion</a>
                @endif
            </div>
            <div class="clearfix"></div>
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