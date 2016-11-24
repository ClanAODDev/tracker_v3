<tr><th>Old String</th> <th>New String</th></tr>
@foreach($division->settings()->locality as $oldString => $newString)
    <tr>
        <td class="col-xs-4">
            <input type="text" name="locality[{{ $oldString }}]" class="form-control"
                   value="{{ $oldString }}" disabled/>
        </td>

        <td class="col-xs-8">
            <input type="text" name="locality[{{ $newString }}]" class="form-control"
                   value="{{ $newString }}" required/>
        </td>
    </tr>
@endforeach