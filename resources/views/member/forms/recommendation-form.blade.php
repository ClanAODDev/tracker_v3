@include('application.partials.errors')

<form action="{{ route('division.recommendations.store', $division) }}" method="post">
    @method('put')
    @csrf

    <div class="panel panel-filled">
        <div class="panel-heading">Make Recommendation</div>
        <div class="panel-body">
            <div class="form-group {{ $errors->has('body') ? ' has-error' : null }}">
                {!! Form::label('body', 'Content', ['class' => 'slight text-muted']) !!}
                {!! Form::textarea('body', null, ['class' => 'form-control resize-vertical', 'required', 'rows' => 2]) !!}
            </div>
        </div>
    </div>

</form>