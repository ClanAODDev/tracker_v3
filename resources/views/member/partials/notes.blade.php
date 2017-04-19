@forelse ($notes as $note)
    <div class="panel panel-filled note {{ $note['type'] }} collapsed">
        <div class="panel-heading text-uppercase panel-toggle">

            @if ($note['type'] == 'sr_ldr')
                <span class="badge text-uppercase">SGT+</span>
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
            <span class="author text-muted">{{ $note->author->name }}</span>
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
    Member has no notes
@endforelse

<button type="button" class="btn btn-default pull-right"
        data-toggle="modal" data-target="#create-member-note">Add note
</button>

{{-- add note modal form --}}
<div class="modal fade" id="create-member-note">
    <div class="modal-dialog" role="document" style="background-color: #000;">
        @include('member.forms.add-note-form')
    </div>
</div>

@if ($errors->count())
    <script>$("#create-member-note").modal();</script>
@endif