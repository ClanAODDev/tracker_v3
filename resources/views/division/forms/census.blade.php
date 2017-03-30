<div class="table-responsive">
    <table class="table table-hover basic-datatable table-striped">
        <thead>
        <tr>
            <th>Date</th>
            <th>Population</th>
            <th>Weekly Active</th>
            <th>Notes</th>
        </tr>
        </thead>
        @foreach ($censuses as $census)
            <tr>
                <td>{{ $census->created_at->format('m/d/Y') }}</td>
                <td>{{ $census->count }}</td>
                <td>{{ $census->weekly_active_count }}</td>
                <td>{{ $census->notes }}</td>
            </tr>
        @endforeach
    </table>
</div>