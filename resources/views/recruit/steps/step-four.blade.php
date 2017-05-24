<div class="step-container step-four">
    @include ('recruit.partials.member-status-request')
    @include ('recruit.partials.create-welcome-post')

    <a href="{{ route('recruiting.stepFive', $division->abbreviation) }}" class="btn btn-success pull-right">Finish</a>
    <button class="pull-left btn btn-default" type="button" onclick="history.back()">Back</button>
</div>