@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $division->name }}
        @endslot
        @slot ('subheading')
            Bulk Messaging
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('send-private-message', $division) !!}

        <p>The AOD Forums has a maximum number of <code>20</code> recipients per PM. To assist with this limitation,
            members have been chunked into groups for your convenience.</p>

        <div class="panel panel-c-accent panel-filled m-t-md">
            <div class="panel-heading">
                Message Groups <small class="text-muted">{{ $members->count() }} recipients</small>
            </div>
            <div class="panel-body">
                @foreach ($members->chunk(20) as $chunk)
                    <a href="{{ doForumFunction($chunk->pluck('clan_id')->toArray(), 'pm') }}"
                       target="_blank" class="btn btn-default pm-link" onclick="$(this).addClass('visited')">
                        <i class="fa fa-link text-accent"></i> Group {{ $loop->iteration }}
                    </a>
                @endforeach
            </div>

            <div class="panel-footer">
                @if ($omitted->count())
                    <p><strong>Note:</strong> Some members ({{ $omitted->count() }}) were filtered out because they do
                        not accept PMs from forum administrators:</p>
                    <p class="text-muted">{{ $omitted->pluck('name')->implode(', ') }}</p>
                @endif
            </div>
        </div>

        @can('remindActivity', \App\Models\Member::class)
        <div class="panel panel-filled m-t-md">
            <div class="panel-heading">
                <i class="fa fa-bell"></i> Inactivity Reminder Tracking
            </div>
            <div class="panel-body">
                <p class="text-muted">If this is an inactivity reminder, mark these members as reminded to track follow-ups.</p>
                <form action="{{ route('bulk-reminder.store', $division) }}" method="POST" id="pm-reminder-form">
                    @csrf
                    <input type="hidden" name="member_ids" value="{{ $members->pluck('clan_id')->implode(',') }}">
                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="confirm" id="pm-reminder-confirm" required>
                            Mark {{ $members->count() }} member{{ $members->count() !== 1 ? 's' : '' }} as reminded
                        </label>
                    </div>
                    <button type="submit" class="btn btn-success m-t-sm" id="pm-reminder-btn" disabled>
                        <i class="fa fa-bell"></i> Set Reminder Date
                    </button>
                </form>
            </div>
            @if(session('reminder_result'))
                <div class="panel-footer">
                    <div class="alert alert-success m-b-none">
                        <i class="fa fa-check"></i>
                        {{ session('reminder_result.count') }} member{{ session('reminder_result.count') !== 1 ? 's' : '' }} marked as reminded.
                        @if(session('reminder_result.skipped') > 0)
                            <span class="text-muted">({{ session('reminder_result.skipped') }} skipped - already reminded today)</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        @endcan

        <a href="{{ url()->previous() }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Go back</a>

    </div>

@endsection

@section('footer_scripts')
<script>
    $(function() {
        $('#pm-reminder-confirm').on('change', function() {
            $('#pm-reminder-btn').prop('disabled', !this.checked);
        });
    });
</script>
@endsection
