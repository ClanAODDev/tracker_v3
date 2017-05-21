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
        @include ('application.components.progress-bar', ['percent' => 60])

        <h4><i class="fa fa-check-circle-o text-accent"></i> Step 3: Finishing Steps</h4>

        <p>You are almost finished with your recruit. Below are tasks required by your division in order to in-process your new member.</p>

        @if ($isTesting)
            <div class="alert alert-info slight">Testing Mode - To-do items bypassed</div>
        @endif

        <form action="{{ route('recruiting.stepFour', $division->abbreviation) }}" method="post" id="step-four-form">
            <input type="hidden" name="member_id" value="{{ $request->member_id }}">
            {{ csrf_field() }}
        </form>

        <table class="table table-hover table-bordered table-striped tasks m-b-xl">
            @foreach ($division->settings()->recruiting_tasks as $task)
                <tr>
                    <td class="text-center">

                        <input type="checkbox" name="tasks[]"
                               id="task-{{ $loop->index }}" {{ ($isTesting) ? "checked" : null }} />

                    </td>
                    <td>
                        {{ $task['task_description'] }}</label>
                    </td>
                </tr>
            @endforeach
        </table>

        @include('recruit.partials.ts-info')

        <hr />

        <button type="button" class="btn btn-success step-three-submit pull-right">Continue</button>
        <button class="pull-left btn btn-default" type="button" disabled>Back</button>
    </div>

    <script>
        $(document).ready(function () {
            $('.tasks tr').click(function (event) {
                if (event.target.type !== 'checkbox') {
                    $(':checkbox', this).trigger('click');
                }

                $(".step-three-submit").click(function () {
                    if ($('.tasks :checkbox:checked').length < $('.tasks :checkbox').length) {
                        toastr.error('You must mark all listed tasks complete!', 'Oops');
                        return false;
                    }

                    $("#step-four-form").submit();
                })
            });


        });
    </script>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop