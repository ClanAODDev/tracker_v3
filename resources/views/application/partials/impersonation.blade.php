<div class="container-fluid m-b-md">
    @if(session('impersonating'))
        <x-notice
            type="danger"
            icon="fa-user-secret"
            :cta="route('end-impersonation')"
            ctaLabel="End Impersonation"
        >
            Currently impersonating user: <strong>{{ auth()->user()->name }}</strong>
        </x-notice>
    @endif

    @if(auth()->user()->isImpersonatingRole())
        <x-notice
            type="warning"
            icon="fa-mask"
            :cta="route('end-role-impersonation')"
            ctaLabel="End Role View"
        >
            Viewing as role: <strong>{{ auth()->user()->getEffectiveRole()->getLabel() }}</strong>
        </x-notice>
    @endif
</div>