@foreach($division->settings()->get('recruiting_tasks') as $task)
    <tr data-repeater-item>

        <td class="col-xs-11">

            <div class="input-group">
                <i class="fa fa-bars input-group-addon text-muted" style="cursor: move"></i>
                <input type="text" name="tasks[{{ $loop->index }}][task_description]"
                       class="form-control" placeholder="Task description"
                       value="{{ $task['task_description'] }}" required />
            </div>

        </td>

        <td class="col-xs-1">
            <button type="button" data-repeater-delete class="btn btn-danger">
                <i class="fa fa-trash-o fa-lg"></i></button>
        </td>

    </tr>

@endforeach