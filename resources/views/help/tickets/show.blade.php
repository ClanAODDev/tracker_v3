@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin Support
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Ticket Index
        @endslot
    @endcomponent

    <div class="container-fluid">

        @include('application.partials.errors')

        <div class="panel panel-filled panel-c-{{ $ticket->stateColor }}">
            <div class="panel-heading" style="display: flex; justify-content: space-between">
                <h4><code>#{{ $ticket->id }}</code>
                    <div class="label label-{{ $ticket->stateColor }}">{{ strtoupper($ticket->state) }}</div>
                </h4>

                @can('manage', $ticket)
                    <div style="display: inline-flex;">
                        <form action="{{ route('help.tickets.assign-to', $ticket) }}" method="post" id="assignTicketTo">
                            {{ method_field('PATCH') }}
                            {{ csrf_field() }}
                            <select class="form-control" name="owner_id" id="owner_id">
                                <option hidden disabled selected>Assign to...</option>
                                @foreach (\App\Models\User::admins()->get()->except(auth()->id()) as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        @if (!$ticket->owner || $ticket->owner_id != auth()->id())
                            <form action="{{ route('help.tickets.self-assign', $ticket) }}" method="POST"
                                  class="inline" style="margin-left: 10px;">
                                <button class="btn btn-info" type="submit">Assign to me</button>
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}
                            </form>
                        @endif
                        @unless ($ticket->isResolved())
                            <form action="{{ route('help.tickets.resolve', $ticket) }}" method="POST"
                                  class="inline" style="margin-left: 10px;">
                                <button class="btn btn-success" type="submit">Resolve Ticket</button>
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}
                            </form>

                            <form action="{{ route('help.tickets.reject', $ticket) }}" method="POST"
                                  class="inline" style="margin-left: 10px;">
                                <button type="submit"
                                        onclick="return confirm('Are you sure you wish to reject this message? Please be sure you include comments in the discussion explaining the reason for the rejection.')"
                                        class="btn btn-danger"><i class="fas fa-times"></i> Reject
                                </button>
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}
                            </form>
                        @else
                            <form action="{{ route('help.tickets.reopen', $ticket) }}" method="POST" class="inline"
                                  style="margin-left: 10px;">
                                <button class="btn btn-accent" type="submit">Reopen Ticket</button>
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}
                            </form>
                        @endif
                    </div>
                @endcan

            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="caller">Caller:</label>
                            <input type="text" id="caller" name="caller" class="form-control"
                                   style="color: rgba(255,255,255,.6)" disabled
                                   value="{{ $ticket->caller->name }} ({{ $ticket->caller->member->position->name() }} of {{ $ticket->division->name }})">
                        </div>

                        @if ($ticket->owner)
                            <div class="form-group">
                                <label for="caller">Assigned to:</label>
                                <input type="text" id="caller" name="caller" class="form-control"
                                       style="color: rgba(255,255,255,.6)" disabled
                                       value="{{ $ticket->owner->name }}">
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="ticket_type">Ticket Type</label>
                            <input type="text" class="form-control" id="ticket_type" name="ticket_type"
                                   style="color: rgba(255,255,255,.6)" disabled
                                   value="{{ $ticket->type->name }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="created">Created At:</label>
                            <input type="text" id="created" name="created" class="form-control"
                                   style="color: rgba(255,255,255,.6)" disabled
                                   value="{{ $ticket->created_at }}">
                        </div>
                        <div class="form-group">
                            <label for="created">Updated At:</label>
                            <input type="text" id="updated" name="updated" class="form-control"
                                   style="color: rgba(255,255,255,.6)" disabled
                                   value="{{ $ticket->updated_at }}">
                        </div>
                        @if ($ticket->isResolved())
                            <div class="form-group">
                                <label for="created">Resolved At:</label>
                                <input type="text" id="updated" name="updated" class="form-control"
                                       style="color: rgba(255,255,255,.6)" disabled
                                       value="{{ $ticket->updated_at }}">
                            </div>
                        @endif
                    </div>

                </div>

                <div class="form-group">
                    <label for="description">Ticket Description</label>
                    <textarea name="description" id="description"
                              style="resize: vertical; min-height: 140px; color: rgba(255,255,255,.6)" disabled
                              class="form-control">{{ $ticket->description }}</textarea>
                </div>
            </div>
        </div>


        <hr>

        <h4><i class="fas fa-comments"></i> Discussion</h4>


        <form action="{{ route('help.tickets.comments.store', $ticket) }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group">
                <textarea type="text" class="form-control" name="comment" minlength="5" id="comment"
                          required></textarea>
            </div>
            <div class="text-right form-group">
                <button type="submit" class="btn btn-primary">Post message</button>
            </div>
        </form>

        @foreach ($ticket->comments as $comment)
            <div class="panel panel-filled {{ $comment->user->isRole('admin') ? 'panel-c-danger' : null }}">
                <div class="panel-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong {{ $comment->user->isRole('admin') ? "class=text-danger" : null }}>{{ $comment->user->name }}</strong>
                            {{ \OsiemSiedem\Autolink\Facades\Autolink::convert($comment->body) }}
                        </div>
                        <div class="text-muted">{{ $comment->created_at->diffForHumans() }}
                            | {{ $comment->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <script>
        $("#owner_id").change(function () {
            let selected = $(this).children("option:selected").text();
            if (confirm("Are you sure you want to assign " + selected + "?")) {
                $("#assignTicketTo").submit();
            }
        });
    </script>

@endsection
