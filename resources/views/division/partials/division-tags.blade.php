@forelse($division->tags as $tag)
    <div class="row form-group" data-repeater-item>
        <div class="col-xs-11">
            <input type="text" name="tags[{{ $loop->index+1 }}][tag]"
                   class="form-control" placeholder="Tag" value="{{ $tag->name }}" required />
        </div>
        <div class="col-xs-1">
            <button type="button" data-repeater-delete class="btn btn-danger">
                <i class="fa fa-trash-o fa-lg"></i></button>
        </div>
    </div>
@empty
    <div class="row form-group" data-repeater-item>
        <div class="col-xs-11">
            <input type="text" name="tags[0][tag]"
                   class="form-control" placeholder="Tag" required />
        </div>
        <div class="col-xs-1">
            <button type="button" data-repeater-delete class="btn btn-danger">
                <i class="fa fa-trash-o fa-lg"></i></button>
        </div>
    </div>
@endforelse

