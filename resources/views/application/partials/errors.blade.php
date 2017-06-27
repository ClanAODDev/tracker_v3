@if($errors->count())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">
            Error: {{ $error }}
        </div>
    @endforeach
@endif