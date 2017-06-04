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
                <td>{{ $handle->label }}</td>
                <td>
                    <div class="label label-default">{{ $handle->divisions_count }}</div>
                </td>
                <td>
                    @if ($handle->url)
                        <code>{{ $handle->url }}</code>
                    @endif
                </td>
                <td class="slight text-uppercase text-{{ $handle->visible ? 'success' : 'muted' }}"
                    title="Visible on member profile">
                    {{ var_export($handle->visible, true) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>