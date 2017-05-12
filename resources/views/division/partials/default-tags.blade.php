@foreach($defaultTags as $tag)
    <div class="col-xs-3">
        <div class="input-group form-group">
            <input type="text" class="form-control" value="{{ $tag->name }}" disabled />
        </div>
    </div>
@endforeach