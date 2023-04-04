@include('application.partials.errors')

<div class="panel panel-filled">

    <div class="panel-heading">Make Recommendation</div>
    <div class="panel-body">

        <p>Recommend this member for a promotion or demotion. Recommendations will
            be submitted to division leadership based on the effective date. Note that you can only recommend
            members for promotion up to your <strong class="text-accent">current rank and below</strong>.</p>

        <p>* All fields are required</p>

        <div class="form-group">
            {!! Form::label('rank_id', 'Rank*') !!}
            {!! Form::select('rank_id', $recommendableRanks, $member->rank_id, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group {{ $errors->has('body') ? ' has-error' : null }}">
            {!! Form::label('justification', 'Justification*') !!}
            {!! Form::textarea('justification', null, ['class' => 'form-control
            resize-vertical', 'required', 'rows' => 3, 'placeholder' => 'Please provide a brief justification']) !!}
        </div>

        <div class="form-group {{ $errors->has('body') ? ' has-error' : null }}">
            {!! Form::label('effective_at', 'Effective Date*') !!}
            {!! Form::date('effective_at', now(), ['class' => 'form-control', 'required']) !!}
            <p class="help-block slight">Select a future month to hold the recommendation until that month arrives.</p>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-default btn-block" style="margin-top:23px">Submit for review</button>
        </div>
    </div>
</div>

