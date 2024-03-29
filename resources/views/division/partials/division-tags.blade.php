@forelse ($division->tags as $tag)
    <div class="col-sm-6" data-repeater-item>
        <div class="form-group">
            <input type="text" name="tags[{{ $loop->index }}][tag]" maxlength="16"
                   class="form-control" placeholder="Enter a tag..." value="{{ $tag->name }}" required />
            <button data-repeater-delete class="btn-xs btn" type="button"
                    style="position: absolute; top: 5px; right: 10px;">
                <i class="fa fa-times fa-lg"></i>
            </button>
        </div>
    </div>
@empty
    <div class="col-sm-6" data-repeater-item>
        <div class="form-group">
            <input type="text" name="tags[0][tag]" maxlength="16"
                   class="form-control" placeholder="Enter a tag..." required />
            <button data-repeater-delete class="btn-xs btn" type="button"
                    style="position: absolute; top: 5px; right: 10px;">
                <i class="fa fa-times fa-lg"></i>
            </button>
        </div>
    </div>
@endforelse
