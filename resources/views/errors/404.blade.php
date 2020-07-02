@extends('application.base')

@section('content')

    <div class="container-center md">
        @component('application.components.view-heading')
            @slot('heading')
                Not Found
            @endslot
            @slot('subheading')
                This is not the page you're looking for
            @endslot
            @slot('icon')
                <i class="pe page-header-icon pe-7s-close-circle"></i>
            @endslot
            @slot('currentPage')
                v3
            @endslot
        @endcomponent

        <div class="panel panel-filled">
            <div class="panel-body">
                Either something broke, or you have poor typing skills. Either way, the page you tried to reach does not exist. If you feel this is in error, please contact your division leadership or a clan administrator.
            </div>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn btn-accent">Back to app</a>
        </div>





{{--                @if(app()->bound('sentry') && !empty(Sentry::getLastEventID()))--}}
                    <div class="subtitle">Error ID: {{ Sentry::getLastEventID() }}</div>

                    <!-- Sentry JS SDK 2.1.+ required -->
                    <script src="https://cdn.ravenjs.com/3.3.0/raven.min.js"></script>

                    <script>
                        Raven.showReportDialog({
                            eventId: '{{ Sentry::getLastEventID() }}',
                            // use the public DSN (dont include your secret!)
                            dsn: 'https://2a218d8004d84610bfa58ba2ec13e74f@o166180.ingest.sentry.io/1238719',
                            user: {
                                'name': 'Jane Doe',
                                'email': 'jane.doe@example.com',
                            }
                        });
                    </script>
{{--                @endif--}}
            </div>






    </div>

@endsection
