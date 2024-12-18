@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $squad->name ?? "Untitled " . $division->locality('squad') }}
            @include('squad.partials.edit-squad-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('squad', $division, $platoon, $squad) !!}

        @include('division.partials.select-panel')

        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-filled ld-loading">
                    <div class="loader">
                        <div class="loader-bar"></div>
                    </div>
                    @include('squad.partials.squad-members')
                </div>
            </div>
            <div class="col-md-2">
                @include('platoon.partials.squads')
                @include('squad.partials.member-stats')
            </div>
        </div>
    </div>


    @component('application.components.modal', ['showSaveButton' => false])
        @slot('title')Mass Forum PM ({{ count($squad->members) }}) @endslot
        @slot('body')
            <p>The Clan AOD forums has a maximum number of 20 recipients per PM. To assist with this limitation, members have been chunked into groups for your convenience.</p>
            <p class="m-t-md">
                @foreach ($squad->members->chunk(20) as $chunk)
                    <a href="{{ doForumFunction($chunk->pluck('clan_id')->toArray(), 'pm') }}"
                       target="_blank" class="btn btn-default">
                        <i class="fa fa-link text-accent"></i> Group {{ $loop->iteration }}
                        ({{ count($chunk) }})
                    </a>
                @endforeach
            </p>
        @endslot
    @endcomponent
@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js?v=2.21') !!}"></script>
@endsection
