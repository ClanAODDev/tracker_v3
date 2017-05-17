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
        <p>Depending on your division configuration, members must be assigned to a {{ $division->locality('platoon') }} and {{ $division->locality('squad') }}. For convenience, your current assignment has been preselected. Changing the {{ $division->locality('platoon') }} will automatically update the list of {{ str_plural($division->locality('squad')) }} available.</p>

        <form action="{{ route('stepOne', [$division->abbreviation]) }}" method="post" class="m-t-lg">

            {{ csrf_field() }}

            <div class="form-group">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        Information
                    </div>
                    <div class="panel-body">
                        {{-- member info --}}
                        <table class="table">
                            <tr>
                                <td>
                                    <label for="name">Forum Name</label>
                                    <input type="text" class="form-control" name="name" id="forum-name" required>
                                </td>
                                <td>
                                    <label for="name">Ingame Name</label>
                                    <input type="text" class="form-control" name="name" id="ingame-name" required>
                                </td>
                                <td>
                                    <label for="name">Forum Id</label>
                                    <input type="number" class="form-control" name="name" id="member-id" required>
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

                <div class="panel panel-filled">
                    <div class="panel-heading">Assignment</div>
                    <div class="panel-body">
                        <table class="table">
                            <tr>
                                <td class="col-xs-6">
                                    <label for="platoon">{{ $division->locality('platoon') }}</label>
                                    <select name="platoon" id="platoon" class="form-control">
                                        <option value="">Select a platoon...</option>
                                        @foreach ($division->platoons as $platoon)
                                            <option value="{{ $platoon->id }}">
                                                {{ $platoon->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="col-xs-6">
                                    <label for="squad">{{ $division->locality('squad') }}</label>
                                    <select name="squad" id="squad" class="form-control" disabled>
                                        <option>Select a platoon...</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </form>

        <button type="submit" class="btn btn-success pull-right">Continue <i class="fa fa-arrow-right"></i></button>
    </div>

    <script>
        $("#forum-name").change(function () {
            $("#ingame-name").val($(this).val()).effect('highlight');
        });

        $("#platoon").change(function () {
            let platoon = $(this).val(),
                base_url = window.Laravel.appPath;

            $.post(base_url + "/search-platoon",
                {
                    platoon: platoon,
                    _token: $('meta[name=csrf-token]').attr('content')
                },
                function (data) {
                    var options = $("#squad");

                    options.empty().attr('disabled', 'disabled');

                    $.each(data, function (name, id) {
                        if (!name) {
                            name = "Squad #" + id
                        }
                        options.append(new Option(name, id));
                    });

                    if (Object.keys(data).length < 1) {
                        options.append(new Option('No Squads Available'));
                        return false;
                    }

                    options.removeAttr('disabled').effect('highlight');
                })
        });
    </script>

@stop