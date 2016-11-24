@foreach($division->settings()->get('recruiting_threads') as $thread)
    <tr class="{{ ($thread['default']) ? "default-item" : null }}" data-repeater-item>

        <td class="col-xs-7">
            <input type="text" name="threads[{{ $loop->index }}][thread_name]"
                   class="form-control" placeholder="Thread Name"
                   value="{{ $thread['thread_name'] }}" required/>
        </td>

        <td class="col-xs-4">
            <input type="number" name="threads[{{ $loop->index }}][thread_id]"
                   class="form-control" placeholder="Thread ID"
                   value="{{ $thread['thread_id'] }}" required/>
        </td>

        <td class="col-xs-2">
            <button type="button" data-repeater-delete class="btn btn-danger">
                <i class="fa fa-trash-o fa-lg"></i></button>
        </td>
    </tr>
@endforeach
