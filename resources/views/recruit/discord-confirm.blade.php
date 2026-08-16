@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Confirm Discord Recruit
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @if ($pendingUser)
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <i class="fab fa-discord" style="color: #5865F2;"></i> Pending Discord Registration
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4 application-field">
                            <div class="application-field-label">Discord Username</div>
                            <div class="application-field-value">{{ $pendingUser['discord_username'] }}</div>
                        </div>
                        <div class="col-md-4 application-field">
                            <div class="application-field-label">Applied</div>
                            <div class="application-field-value">{{ $pendingUser['created_at'] }}</div>
                        </div>
                        <div class="col-md-4 application-field">
                            <div class="application-field-label">Application Division</div>
                            <div class="application-field-value">{{ $pendingUser['application_division'] ?? 'None on file' }}</div>
                        </div>
                    </div>

                    @if ($pendingUser['application'])
                        <hr>
                        <h5>Application Responses</h5>
                        <div class="row">
                            @foreach ($pendingUser['application'] as $item)
                                <div class="col-md-4 application-field">
                                    <div class="application-field-label">{{ $item['label'] }}</div>
                                    <div class="application-field-value">{{ $item['value'] ?: '—' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="panel-footer text-right">
                    <a href="{{ route('recruiting.form', $division) }}?pending_user_id={{ $pendingUser['id'] }}" class="btn btn-success">
                        Start Recruitment <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                No pending Discord registration found for this account (ID: <code>{{ $discordId }}</code>). They may need to register with the AOD Discord bot before they can be recruited.
            </div>
            <a href="{{ route('recruiting.form', $division) }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Recruitment Form
            </a>
        @endif
    </div>
@endsection
