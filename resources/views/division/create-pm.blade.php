@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
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

        <a href="{{ url()->previous() }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Go back</a>


    </div>

@endsection
