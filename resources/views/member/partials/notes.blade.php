@can('create', App\Models\Note::class)
    <div class="modal fade" id="notes-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">
                        Member Notes
                        @if(count($notes))
                            <span class="badge">{{ count($notes) }}</span>
                        @endif
                    </h4>
                    @if(auth()->user()->isRole(['sr_ldr', 'admin']) && $trashedNotes->count() > 0)
                        <button type="button" class="btn btn-xs btn-default pull-right m-t-xs toggle-trashed-notes" data-count="{{ $trashedNotes->count() }}">
                            <i class="fa fa-trash"></i> Deleted ({{ $trashedNotes->count() }})
                        </button>
                    @endif
                </div>
                <div class="modal-body">
                    <div class="notes-active-list">
                        @if (count($notes))
                            <div class="notes-list">
                                @foreach ($notes as $note)
                                    @include ('member.partials.note')
                                @endforeach
                            </div>
                        @else
                            <div class="text-center p-lg">
                                <i class="fa fa-sticky-note fa-3x text-muted m-b-md" style="opacity: 0.3;"></i>
                                <p class="text-muted">No notes recorded for this member.</p>
                            </div>
                        @endif
                    </div>
                    @if(auth()->user()->isRole(['sr_ldr', 'admin']) && $trashedNotes->count() > 0)
                        <div class="notes-trashed-list" style="display: none;">
                            <div class="alert alert-warning m-b-md">
                                <i class="fa fa-exclamation-triangle"></i>
                                These notes have been deleted and are only visible to senior leaders.
                            </div>
                            <div class="notes-list">
                                @foreach ($trashedNotes as $note)
                                    @include ('member.partials.note', ['trashed' => true])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-accent" data-toggle="modal" data-target="#create-member-note" data-dismiss="modal">
                        <i class="fa fa-plus"></i> Add Note
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="create-member-note" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('storeNote', [$member->clan_id]) }}" method="post">
                @csrf
                @include('member.forms.create-note-form', ['action' => 'Add Member Note', 'create' => true])
            </form>
        </div>
    </div>

    @if ($errors->count())
        <script>$('#create-member-note').modal();</script>
    @endif

    <script>
        $(function() {
            if (new URLSearchParams(window.location.search).get('notes') === '1') {
                $('#notes-modal').modal('show');
            }
        });
    </script>
@endcan
