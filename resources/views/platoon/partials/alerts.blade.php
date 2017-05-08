{{-- Does platoon have any members not assigned to a squad? --}}
@if (count($platoon->unassigned))
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        This {{ $division->locality('platoon') }} has unassigned members. To assign them, either edit an existing {{ $division->locality('squad') }}, or create a new one.
    </div>
@endif