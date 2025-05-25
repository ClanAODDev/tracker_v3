{{-- Does platoon have any members not assigned to a squad? --}}
@if (count($platoon->unassigned))
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        This {{ $division->locality('platoon') }} has <code>{{ count($platoon->unassigned) }}</code> unassigned members.
    </div>
@endif