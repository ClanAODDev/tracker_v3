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
        <td><code>division.name</code></td>
        <td>the division's name</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>division.platoons</code></td>
        <td>collection of a division's platoons</td>
        <td><code>object</code></td>
    </tr>
    <tr>
        <td><code>division.division</code></td>
        <td>A collection of all active members</td>
        <td><code>array</code></td>
    </tr>
    <tr>
        <td><code>division.partTimeMembers</code></td>
        <td>A collection of all part-time members</td>
        <td><code>array</code></td>
    </tr>
    <tr>
        <td><code>division.locality</code></td>
        <td>Accessor for localization. Available localizations:<br />
            <code>division.locality.squad</code><br />
            <code>division.locality.platoon</code><br />
            <code>division.locality.squad_leader</code><br />
            <code>division.locality.platoon_leader</code>
        </td>
        <td><code>array</code></td>
    </tr>
    <tr>
        <td><code>division.leave</code></td>
        <td>Accessor for leaves of absence. Treat these as member objects. Ex.,
            <code>for member in division.leave</code>
        </td>
        <td><code>array</code></td>
    </tr>
    </tbody>
</table>