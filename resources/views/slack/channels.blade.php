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
            Manage Slack Channels
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="alert alert-warning">
            <strong>Note</strong>: Archiving a channel hides it from view, but does not delete the channel. An admin must physically delete the channel through the Slack interface.
        </div>

        <p>Division channels should be used sparingly. Advise your members that they can <code><i class="fa fa-star text-accent"></i> Star</code> a channel to make it easier to see in the channel listing.</p>

        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-filled">
                    <div class="panel-heading"><i class="fa fa-slack"></i> Manage Slack Channels</div>
                    <div class="panel-body">
                        @if (count($channels))
                            @include('slack.partials.slack-channels')
                        @else
                            <p>You have no channels. Use the form to the right to create one.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-filled panel-c-info">
                    <div class="panel-heading">Create Channel</div>
                    <div class="panel-body">
                        <form action="{{ route('slack.store-channel') }}" method="post">
                            @if (auth()->user()->isRole('admin'))
                                @include('slack.forms.create-channel')
                            @else
                                @include('slack.forms.division-create-channel')
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop