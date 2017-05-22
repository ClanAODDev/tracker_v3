<table class="table table-hover table-bordered table-striped tasks m-b-xl">
    @foreach ($division->settings()->recruiting_tasks as $task)
        <tr>
            <td class="text-center">
                <input type="checkbox" name="tasks[]"
                       id="task-{{ $loop->index }}" {{ ($isTesting) ? "checked" : null }} />
            </td>
            <td>
                {{ $task['task_description'] }}
            </td>
        </tr>
    @endforeach
</table>