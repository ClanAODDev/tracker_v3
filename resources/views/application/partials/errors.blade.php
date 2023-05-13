@if($errors->count())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">
            <h4>Heads up!</h4>
            <p>{{ $error }}</p>
        </div>
    @endforeach
@endif