@include('application.partials.errors')

<div class="row">
    <div class="col-xs-6">
        <div class="form-group {{ $errors->has('end_date') ? ' has-error' : null }}">
            {!! Form::label('end_date', 'Leave End Date') !!}<span class="text-accent">*</span>
            {{ Form::date('end_date', $leave->end_date->format('Y-m-d'), ['class' => 'form-control', 'placeholder' => 'mm/dd/yyyy']) }}
        </div>
    </div>

    <div class="col-xs-6">
        <div class="form-group {{ $errors->has('reason') ? ' has-error' : null }}">
            {!! Form::label('reason', 'Leave Type') !!}<span class="text-accent">*</span>
            {!! Form::select('reason', ['military' => 'Military', 'medical' => 'Medical', 'education' => 'Education', 'travel' => 'Travel', 'other' => 'Other'], null, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if (! $leave->approver)
            <div class="form-group">
                <input type="checkbox" id="approve_leave" name="approve_leave" /> <label
                        for="approve_leave">Approve leave of absence?</label>
            </div>
        @endif
    </div>
    <div class="col-md-6 text-right">
        <a href="{{ route('home') }}" class="btn btn-default">Cancel</a>
        <button class="btn btn-success" type="submit">Submit Changes</button>
    </div>
</div>