<div class="row">
    @forelse ($division->tags as $tag)
        <div class="col-xs-3" data-repeater-item>
            <div class="input-group form-group">
                <input type="text" name="tags[{{ $loop->index+1 }}][tag]"
                       class="form-control" placeholder="Enter a tag..." value="{{ $tag->name }}" required />
                <span class="input-group-btn">
                    <button data-repeater-delete class="btn-xs btn" type="button" style="margin-left: -30px">
                        <i class="fa fa-times fa-lg"></i>
                    </button>
                </span>
            </div>
        </div>
    @empty
        <div class="col-xs-3" data-repeater-item>
            <div class="input-group form-group">
                <input type="text" name="tags[0][tag]"
                       class="form-control" placeholder="Enter a tag..." required />
                <span class="input-group-btn">
                    <button data-repeater-delete class="btn-xs btn" type="button" style="margin-left: -30px">
                        <i class="fa fa-times fa-lg"></i>
                    </button>
                </span>
            </div>
        </div>
    @endforelse
</div>

