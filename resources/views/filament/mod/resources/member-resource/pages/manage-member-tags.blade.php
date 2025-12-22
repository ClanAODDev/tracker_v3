<x-filament-panels::page>
    {{ $this->table }}
</x-filament-panels::page>

@push('styles')
<style>
.fi-ta-row { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const row = e.target.closest('tr.fi-ta-row');
        if (!row) return;

        if (e.target.closest('input[type="checkbox"]') || e.target.closest('button') || e.target.closest('a')) {
            return;
        }

        const checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.click();
        }
    });
});
</script>
@endpush
