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

        @include ('recruit.partials.testing-bar')
        @include ('application.components.progress-bar', ['percent' => 60])

        <div class="panel">
            <h3><i class="fa fa-check-circle-o text-accent"></i> Step 3: In-processing</h3>
            <p>You are almost finished with your recruit. Below are tasks required by your division in order to in-process your new member.</p>
        </div>

        <form action="{{ route('recruiting.stepFour', $division->abbreviation) }}" method="post" id="step-four-form">
            <input type="hidden" name="member_id" value="{{ $request->member_id }}">
            {{ csrf_field() }}
        </form>

        <div class="row">
            <div class="col-sm-6">
                @include ('recruit.partials.tasks')
            </div>
            <div class="col-sm-6">
                @include ('recruit.partials.recap-info')
            </div>
        </div>

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
            });

            $(".step-three-submit").click(function () {
                if ($('.tasks :checkbox:checked').length < $('.tasks :checkbox').length) {
                    toastr.error('You must mark all listed tasks complete!', 'Oops');
                    return false;
                }

                $("#step-four-form").submit();
            })


        });
    </script>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop