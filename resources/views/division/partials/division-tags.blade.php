@foreach ($division->tags as $tag)
    <div class="col-xs-6" data-repeater-item>
        <div class="form-group">
            <input type="text" name="tags[{{ $loop->index }}][tag]" maxlength="16"
                   class="form-control" placeholder="Enter a tag..." value="{{ $tag->name }}" required />
            <button data-repeater-delete class="btn-xs btn" type="button" style="position: absolute; top: 5px; right: 10px;">
                <i class="fa fa-times fa-lg"></i>
            </button>
        </div>
    </div>
@endforeach
