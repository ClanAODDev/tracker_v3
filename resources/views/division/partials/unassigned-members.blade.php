<x-unassigned-organizer
    :count="$division->unassigned->count()"
    :unit-type="$division->locality('platoon')"
    :members="$division->unassigned"
    button-class="organize-platoons-btn"
    members-class="unassigned-platoon-member"
    container-class="unassigned-organizer"
    :can-organize="auth()->user()->can('manageUnassigned', App\Models\User::class)"
/>