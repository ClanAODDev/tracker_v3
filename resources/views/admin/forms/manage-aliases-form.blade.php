<table class="table">
    <tr>
        <th>Name</th>
        <th>Abbreviation</th>
        <th>URL</th>
        <th>Visible</th>
    </tr>
    @foreach ($aliases as $alias)
        <tr>
            <td><input type="text" class="form-control" value="{{ $alias->name }}" /></td>
            <td><input type="text" class="form-control" value="{{ $alias->type }}" /></td>
            <td><input type="text" class="form-control" value="{{ $alias->url }}" /></td>
            <td><input type="text" class="form-control" value="{{ var_export($alias->visible, true) }}" /></td>
        </tr>
    @endforeach
</table>