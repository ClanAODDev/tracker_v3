@foreach($division->settings()->get('recruiting_threads') as $thread)
    <table class="table" data-repeater-item>
        <tr>
            <td class="col-xs-6">
                <input type="text" name="threads[{{ $loop->index }}][thread_name]"
                       class="form-control" placeholder="Thread Name"
                       value="{{ $thread['thread_name'] }}" required />
            </td>

            <td class="col-xs-6">
                <input type="number" name="threads[{{ $loop->index }}][thread_id]"
                       class="form-control" placeholder="Thread Id"
                       value="{{ $thread['thread_id'] }}" required />
            </td>

            <td class="col-xs-2">
                <button type="button" data-repeater-delete class="btn btn-danger">
                    <i class="fa fa-trash-o fa-lg"></i></button>
            </td>
        </tr>

        <tr>
            <td class="col-xs-12" colspan="3">
            <textarea name="threads[{{ $loop->index }}][comments]" \
                      style="resize: vertical;" class="form-control"
                      placeholder="Brief comments about the thread..."
            >{!! empty($thread['comments']) ? null : $thread['comments'] !!}</textarea>
            </td>
        </tr>
    </table>
@endforeach
