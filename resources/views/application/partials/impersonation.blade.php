<div class="container-fluid">
    <x-notice
        type="danger"
        icon="fa-user-secret"
        :cta="route('end-impersonation')"
        ctaLabel="End Impersonation"
    >
        Currently impersonating user: <strong>{{ auth()->user()->name }}</strong>
    </x-notice>
</div>