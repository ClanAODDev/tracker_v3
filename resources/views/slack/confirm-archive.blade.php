@extends('application.base')

@section('content')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Confirm Archive of Channel
        @endslot
    @endcomponent

    <div class="container-fluid">

        <h4><i class="fa fa-exclamation-triangle text-danger"></i> Archive Channel</h4>
        <p>You are about to archive the <code>#{{ $channel->getName() }}</code> channel.</p>
        <p> Are you sure?</p>
        <hr />
        @include('application.partials.errors')
        <form action="{{ route('slack.archive-channel') }}"
              method="post" id="archive-channel">
            <input type="hidden" value="{{ $channel->getId() }}" name="channel_id" />
            <a href="{{ route('slack.channel-index') }}" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-success">Archive Channel</button>
            {{ csrf_field() }}
        </form>
@stop


