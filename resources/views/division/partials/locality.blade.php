<tr>
    <th>Replace</th>
    <th>With</th>
</tr>
@foreach($division->settings()->locality as $translation)

    <?php $old_string = ! empty($translation['old-string']) ? $translation['old-string'] : null ?>
    <?php $new_string = ! empty($translation['new-string']) ? $translation['new-string'] : null ?>

    <tr data-locality-entry>
        <td class="col-xs-6">
            <input type="text" name="locality[{{ $loop->index }}][old-string]" class="form-control"
                   value="{{ $old_string }}" data-old-string readonly />
        </td>

        <td class="col-xs-6">
            <input type="text" name="locality[{{ $loop->index }}][new-string]" class="form-control"
                   value="{{ strtolower($new_string) }}" data-new-string required />
        </td>
    </tr>
@endforeach