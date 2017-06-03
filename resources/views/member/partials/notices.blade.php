@if (! $member->handles->contains($division->handle) && $division->handle)
    <div class="alert alert-warning">
        The {{ $division->name }} division requires a
        <code>{{ $division->handle->name }}</code> handle, but {{ $member->name }} does not have one. You should add it now.
    </div>
@endif

