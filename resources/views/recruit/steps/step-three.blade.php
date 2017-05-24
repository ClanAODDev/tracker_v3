<div class="step-container step-three">

    <div class="panel">
        <h3><i class="fa fa-check-circle-o text-accent"></i> Step 3: In-processing</h3>
        <p>You are almost finished with your recruit. Below are tasks required by your division in order to in-process your new member.</p>
    </div>

    <form action="{{ route('recruiting.stepFour', $division->abbreviation) }}" method="post" id="step-four-form">
        {{--<input type="hidden" name="member_id" value="{{ $request->member_id }}">--}}
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