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
        <td><code>member.name</code></td>
        <td>forum name</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.rank.name</code></td>
        <td>full rank (ex. Sergeant, Corporal, etc)</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.rank.abbreviation</code></td>
        <td>shorthand rank (ex. Sgt, Cpl, etc)</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.recruiter_id</code></td>
        <td>forum id of member's recruiter</td>
        <td><code>integer</code></td>
    </tr>
    <tr>
        <td><code>member.position.name</code></td>
        <td>position currently held by member</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.handle.pivot.value</code></td>
        <td>ingame name based on primary division</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.handle.url</code></td>
        <td>ingame name URL based on primary division</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.handle.full_url</code></td>
        <td>helper property combining the URL and the handle value</td>
        <td><code>string</code></td>
    </tr>
    <tr>
        <td><code>member.leave</code></td>
        <td>Leave object populated when the member has an active leave of absence. Available properties for leave are: <code>member.leave.end_date</code>, <code>member.leave.reason</code>, <code>member.leave.extended</code> (true if the LOA has been extended from its original date), <code>member.leave.approved</code> (</td>
        <td><code>string</code></td>
    </tr>
    </tbody>
</table>