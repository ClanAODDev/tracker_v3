<h4>
    Member Notes

    @can('create', App\Models\Note::class)
        <span class="pull-right">
            <a href="#" class="btn-add-note btn btn-default" data-toggle="modal"
               data-target="#create-member-note"><i class="fa fa-comment text-accent"></i> Add note</a>
        </span>
    @endcan

</h4>
<hr />
@if (count($notes))
    <div class="v-timeline">
        @foreach ($notes as $note)
            @include ('member.partials.note')
        @endforeach
    </div>
@else
    <div class="panel panel-filled">
        <div class="panel-body text-muted">
            Member has no notes recorded.
        </div>
    </div>
@endif


@can ('create', App\Models\Note::class)
    <div class="modal fade" id="create-member-note">
        <div class="modal-dialog" role="document" style="background-color: #000;">
            {!! Form::model(App\Models\Note::class, ['method' => 'post', 'route' => ['storeNote', $member->clan_id]]) !!}
            @include('member.forms.note-form', ['action' => 'Add Member Note', 'create' => true])
            {!! Form::close() !!}
        </div>
    </div>
@endcan

@if ($errors->count())
    <script>$('#create-member-note').modal();</script>
@endif

