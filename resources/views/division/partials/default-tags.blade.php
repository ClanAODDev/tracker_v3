@foreach($defaultTags as $tag)
    <div class="col-sm-6">
        <div class="form-group">
            <input type="text" class="form-control" value="{{ $tag->name }}" disabled />
        </div>
    </div>
@endforeach