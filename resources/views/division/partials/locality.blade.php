<tr><th>Old String</th> <th>New String</th></tr>
@foreach($division->settings()->locality as $translation)

    <?php $old_string = ! empty($translation['old-string']) ? $translation['old-string'] : null ?>
    <?php $new_string = ! empty($translation['new-string']) ? $translation['new-string'] : null ?>

    <tr>
        <td class="col-xs-4">
            <input type="text" name="locality[{{ $loop->index }}][old-string]" class="form-control"
                   value="{{ $old_string }}" readonly />
        </td>

        <td class="col-xs-8">
            <input type="text" name="locality[{{ $loop->index }}][new-string]" class="form-control"
                   value="{{ $new_string }}" required/>
        </td>
    </tr>
@endforeach