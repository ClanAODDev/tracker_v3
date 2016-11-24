<tr><th>Old String</th> <th>New String</th></tr>
@foreach($division->settings()->locality as $translation)

    <tr>
        <td class="col-xs-4">
            <input type="text" name="locality[{{ $loop->index }}][old-string]" class="form-control"
                   value="{{ $translation['old-string'] }}" readonly />
        </td>

        <td class="col-xs-8">
            <input type="text" name="locality[{{ $loop->index }}][new-string]" class="form-control"
                   value="{{ $translation['new-string'] }}" required/>
        </td>
    </tr>
@endforeach