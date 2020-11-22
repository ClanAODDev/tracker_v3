<div class="apply-form" style="display: none;">
    <h2>1. Create a forum account</h2>
    <p>You must have a forum account in order to apply for one of our divisions.</p>
    <a href="/forums/register.php" target="_blank"
       class="call-to-action-button is-small">Create an account</a>
    <hr class="margin-top-20"/>
    <h2 class="margin-top-20">2. Apply to a division</h2>
    <div class="games-listing">
        @foreach (\App\Models\Division::active() as $division)
            <a href="#" data-application-id="{{ $division->settings()->get('forum_application_id') }}"
               data-application-link class="tooltips">
                <img class="game" src="{{ getDivisionIconPath($division->abbreviation) }}"/>
                <span>{{ $division->name }}</span>
            </a>
        @endforeach
    </div>
</div>
