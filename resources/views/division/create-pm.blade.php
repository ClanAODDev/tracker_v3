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

        <p>The AOD Forums has a maximum number of <code>20 recipients</code> per PM. To assist with this limitation,
            members
            have been chunked into groups for your convenience.</p>

        <p>Some members may opt out of receiving private messages. This will generate a warning when sending a
            private message. You will need to manually omit them from your mass PM.</p>

        <div class="panel panel-c-accent panel-filled m-t-md">
            <div class="panel-heading">
                Message Groups
            </div>
            <div class="panel-body">
                @foreach ($members->chunk(20) as $chunk)
                    <a href="{{ doForumFunction($chunk->values()->toArray(), 'pm') }}"
                       target="_blank" class="btn btn-default pm-link" onclick="$(this).addClass('visited')">
                        <i class="fa fa-link text-accent"></i> Group {{ $loop->iteration }}
                    </a>
                @endforeach
            </div>

            <div class="panel-footer"><p class="text-muted">Groups will be disabled as they are clicked.</p></div>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Go back</a>


    </div>

@endsection
