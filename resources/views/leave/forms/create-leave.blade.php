<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('member', 'Search for member') !!}
            <input type="text" class="form-control search-member"
                   name="member" id="member" autocomplete="off" />
            <i class="fa fa-search pull-right"
               style="position: absolute; right: 20px; top: 35px;"></i>
        </div>
        <div class="form-group {{ $errors->has('end_date') ? ' has-error' : null }}">
            {!! Form::label('end_date', 'Leave End Date') !!}
            {{ Form::date('end_date', \Carbon\Carbon::now(), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group">
            {!! Form::label('note_thread_id', 'Forum Thread ID') !!}
            {{ Form::number('note_thread_id', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
    <div class="col-md-8">

        <div class="row">
            <div class="col-xs-6">
                <div class="form-group {{ $errors->has('member_id') ? ' has-error' : null }}">
                    {!! Form::label('member_id', 'Member Id' . ':') !!}
                    {!! Form::number('member_id', null, ['class' => 'form-control', 'required' => 'required'] ) !!}
                </div>
            </div>
            <div class="col-xs-6">
                {!! Form::label('leave_type', 'Leave Type') !!}
                {!! Form::select('leave_type', ['military' => 'Military', 'medical' => 'Medical', 'education' => 'Education', 'travel' => 'Travel', 'other' => 'Other'], null, ['class' => 'form-control']) !!}
            </div>
        </div>

        {!! Form::label('note_body', 'Leave Note Body') !!}
        {!! Form::textarea('note_body', null, ['class' => "form-control", 'rows' => 5, 'style' => 'resize: vertical;', 'required' => 'required']) !!}
    </div>
</div>
<button class="btn btn-success pull-right" type="submit">Submit</button>

<script>
  $('#leader').bootcomplete({
    url: window.Laravel.appPath + '/search-leader/',
    minLength: 3,
    idField: true,
    method: 'POST',
    dataParams: {_token: $('meta[name=csrf-token]').attr('content')}
  });
</script>