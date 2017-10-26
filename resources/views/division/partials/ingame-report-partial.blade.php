<?php $file = camel_case($division->name); ?>
@if ($data && file_exists(resource_path("views/division/reports/ingame-reports/{$file}.blade.php")))
    @include("division.reports.ingame-reports.{$file}")
@else
    <h4>No reports</h4>
    <p>If you are seeing this message, no reports exist for the current division</p>
@endif