@foreach($division->settings()->get('recruiting_tasks') as $task)
    <tr data-repeater-item>

        <td class="col-xs-11">
            <input type="text" name="tasks[{{ $loop->index }}][task_description]"
                   class="form-control" placeholder="Add a task"
                   value="{{ $task['task_description'] }}" required />
        </td>

        <td class="col-xs-1">
            <button type="button" data-repeater-delete class="btn btn-danger">
                <i class="fa fa-trash-o fa-lg"></i></button>
        </td>

    </tr>
@endforeach