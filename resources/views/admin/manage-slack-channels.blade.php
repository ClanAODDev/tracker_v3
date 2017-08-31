@extends('application.base')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin CP
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
            <strong>Note</strong>: Archiving a channel hides it from view, but does not delete the channel. The Slack API cannot delete channels; a team owner must physically delete the channel through the Slack interface.
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-filled">
                    <div class="panel-heading"><i class="fa fa-slack"></i> Manage Slack Channels</div>
                    <div class="panel-body">
                        @include('admin.partials.slack-channels')
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-filled panel-c-info">
                    <div class="panel-heading">Create Channel</div>
                    <div class="panel-body">
                        <form action="{{ route('slack.create-channel') }}" method="post">
                            <div class="form-group">
                                <label for="division">Division</label>
                                <select name="division" id="division" class="form-control">
                                    @foreach (\App\Division::active()->get() as $division)
                                        <option value="{{ $division->abbreviation }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="name">Channel Name</label>
                                <input type="text" name="channel-name" class="form-control" autocomplete="off"
                                       onkeyup="updateChannelName();" required />
                            </div>
                            <button type="submit" class="btn btn-default">
                                Create <span class="channel-name-output"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
      function updateChannelName () {
        $('.channel-name-output').text(slugify($('#division').val() + '-' + $('input[name="channel-name"]').val()));
      }

      function slugify (text) {
        return text.toString().toLowerCase().trim()
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .replace(/\s+/g, '-')
          .replace(/&/g, '')
          .replace(/[^\w\-]+/g, '')
          .replace(/\-\-+/g, '-');
      }
    </script>

@stop