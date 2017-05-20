@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            Recruit New Member
        @endslot
    @endcomponent

    <div class="container-fluid">
        <h4><i class="fa fa-paper-plane"></i> Step 2</h4>
        <hr />

        <input type="hidden" name="member-id" value="{{ $request['member-id'] }}">
        <input type="hidden" name="forum-name" value="{{ $request['forum-name'] }}">
        <input type="hidden" name="ingame-name" value="{{ $request['ingame-name'] }}">
        <input type="hidden" name="division-id" value="{{ $request->division->id }}">

        <div class="panel panel-filled">
            <div class="panel-heading">Recruit Member Agreements</div>
            <div class="panel-body">
                <p>AOD members are required to read and reply to a handful of threads posts in the AOD community forums. Your division may have additional threads that you require new members to reply to.</p>
                <button class="btn btn-default refresh-button" onclick="handleThreadCheck()">
                    <i class="fa fa-spinner fa-spin"></i> <span class="status">Loading...</span>
                </button>
            </div>

            <div class="thread-results"></div>
        </div>

        <button class="pull-right btn btn-success">
            Continue
        </button>

    </div>

    <script>
        handleThreadCheck();

        function handleThreadCheck() {
            let base_url = window.Laravel.appPath,
                results = $('.thread-results'),
                loadingIcon = $('.refresh-button i'),
                statusText = $('.status'),
                reloadBtn = $('.refresh-button');

            reloadBtn.attr('disabled','disabled');

            $.ajax({
                url: base_url + "/search-division-threads",
                type: 'POST',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    string: $('input[name=member-id]').val(),
                    division: $('input[name=division-id]').val(),
                },
                cache: false,
                beforeSend: function () {
                    results.empty();
                    loadingIcon.addClass('fa-spin')
                        .addClass('fa-spinner')
                        .removeClass('fa-refresh');
                },
            })

                .done(function (html) {
                    results.empty().prepend(html);
                    loadingIcon.removeClass('fa-spin')
                        .removeClass('fa-spinner')
                        .addClass('fa-refresh');
                    statusText.text('Check Thread Statuses');
                    reloadBtn.removeAttr('disabled');
                });
        }
    </script>

@stop