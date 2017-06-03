<div class="panel">
    <div class="panel-heading">Clan-wide Tags</div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table basic-datatable table-hover table-striped">
                <thead>
                <tr>
                    <th>Tag Id</th>
                    <th>Tag Name</th>
                    <th>Division</th>
                    <th>Note Count</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($allTags as $tag)
                    <tr>
                        <td>{{ $tag->id }}</td>
                        <td>{{ $tag->name }}</td>
                        <td>
                            @if ($tag->division)
                                <a href="{{ route('editDivision', $tag->division->abbreviation) }}#division-tags">
                                    <i class="fa fa-tags"></i>
                                </a>
                            @endif
                            {{ $tag->division->name or "Default" }}
                        </td>
                        <td>
                        <span class="label label-default">
                                {{ count($tag->noteCount) }}
                            </span>
                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>