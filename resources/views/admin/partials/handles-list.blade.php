<div class="table-responsive">
    <table class="table basic-datatable">
        <thead>
        <tr>
            <th class="no-sort"></th>
            <th>Name</th>
            <th>Divisions</th>
            <th>URL</th>
            <th>Visible</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($handles as $handle)
            <tr>
                <td>
                    <a title="Edit Handle" class="btn btn-default"
                       href="{{ route('adminEditHandle', $handle->id) }}">
                        <i class="fa fa-wrench"></i>
                    </a>
                </td>
                <td>{{ $handle->name }}</td>
                <td>
                    @foreach ($handle->divisions as $division)
                        <a href="{{ route('division', $division->abbreviation) }}"
                           class="label label-default text-uppercase slight m-r-xs"
                           title="{{ $division->name }}"
                        >
                            {{ $division->abbreviation }}
                        </a>
                    @endforeach
                </td>
                <td>{{ $handle->url }}</td>
                <td>{{ var_export($handle->visible, true) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>