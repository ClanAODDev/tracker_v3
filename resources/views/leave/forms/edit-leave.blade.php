@include('application.partials.errors')

<div class="row">
    <div class="col-xs-6">
        <div class="form-group {{ $errors->has('end_date') ? ' has-error' : null }}">
            {!! Form::label('end_date', 'Leave End Date') !!}
            {{ Form::date('end_date', $leave->end_date->format('Y-m-d'), ['class' => 'form-control', 'placeholder' => 'mm/dd/yyyy']) }}
        </div>
    </div>

    <div class="col-xs-6">
        <div class="form-group {{ $errors->has('leave_type') ? ' has-error' : null }}">
            {!! Form::label('leave_type', 'Leave Type') !!}
            {!! Form::select('leave_type', ['military' => 'Military', 'medical' => 'Medical', 'education' => 'Education', 'travel' => 'Travel', 'other' => 'Other'], null, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>
<button class="btn btn-success pull-right" type="submit">Submit Changes</button>