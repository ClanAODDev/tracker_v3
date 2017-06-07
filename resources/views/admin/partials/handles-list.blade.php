<div class="table-responsive">
    <table class="table basic-datatable">
        <thead>
        <tr>
            <th class="no-sort"></th>
            <th>Name</th>
            <th class="text-center">Divisions</th>
            <th>URL</th>
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
                <td class="text-center">
                    {{ $handle->divisions_count }}
                </td>
                <td>
                    @if ($handle->url)
                        <code>{{ $handle->url }}</code>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>