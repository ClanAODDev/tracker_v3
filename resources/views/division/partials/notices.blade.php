@if (! $division->handle)
    <div class="alert alert-danger">
        <i class="fa fa-gamepad"></i>
        <strong>{{ $division->name }}</strong> does not have a primary handle. Contact clan leadership to resolve this.
    </div>
@endif

@if (count($division->unassigned) > 0)
    {{-- only show notice if user is capable of taking action --}}
    @can('create', [App\Platoon::class, $division])
        <div class="alert alert-warning">
            <i class="fa fa-users"></i>
            There are <code>{{ count($division->unassigned) }}</code> unassigned members in this division. Edit a
            <a href="#platoons" class="alert-link smooth-scroll">{{ $division->locality('platoon') }}</a> to assign them
        </div>
    @endcan
@endif
