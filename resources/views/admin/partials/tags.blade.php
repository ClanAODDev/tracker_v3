@foreach($defaultTags as $tag)
    <div class="col-sm-3 col-xs-6" data-repeater-item>
        <div class="input-group form-group">
            <input type="text" name="tags[{{ $loop->index }}][tag]"
                   class="form-control" placeholder="Enter a tag..." value="{{ $tag->name }}" required />
            <span class="input-group-btn">
                    <button data-repeater-delete class="btn-xs btn" type="button" style="margin-left: -30px">
                        <i class="fa fa-times fa-lg"></i>
                    </button>
                </span>
        </div>
    </div>
@endforeach