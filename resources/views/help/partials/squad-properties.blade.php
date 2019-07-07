<table class="table table-bordered table-hover basic-datatable">
    <thead>
    <tr>
        <th>Element</th>
        <th>Description</th>
        <th>Type</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><code>squad.name</code></td>
        <td>Squad's name</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>squad.members</code></td>
        <td>Members of a squad. Note that the squad leader is omitted from this array and should be shown separately. Sorted by rank descending, name ascending (alphabetical)</td>
        <td><code>array</code></td>
    </tr>
    <tr>
        <td><code>squad.gen-pop</code></td>
        <td>Squad's gen-pop status (true, false)</td>
        <td><code>boolean</code></td>
    </tr>
    <tr>
        <td><code>squad.leader</code></td>
        <td>The member assigned as the squad leader (see member)</td>
        <td><code>object</code></td>
    </tr>
    <tr>
        <td><code>squad.logo</code></td>
        <td>URL of the squad's logo (nullable)</td>
        <td><code>string</code></td>
    </tr>
    </tbody>
</table>