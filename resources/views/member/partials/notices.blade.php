@if (! $member->handles->contains($primaryDivision->handle) && $primaryDivision->handle)
    <div class="alert alert-warning">
        The {{ $primaryDivision->name }} division requires a
        <code>{{ $primaryDivision->handle->label }}</code> handle, but {{ $member->name }} does not have one. You should
        <a href="#handles">add it now</a>.
    </div>
@endif

