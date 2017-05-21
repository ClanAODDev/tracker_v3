<h3 class="m-t-xl"><i class="fa fa-mail-reply-all text-accent"></i> Step 5: Create Welcome Post</h3>

@if ($division->settings()->get('welcome_area'))

    @if ($division->settings()->get('use_welcome_thread'))
        <p>Your division uses a welcome thread for all new recruit introductions. Click the button below to create a post and introduce your new recruit to the other members of the division.</p>

        <div class="text-center p-lg">
            <a href="{{ doForumFunction([$division->settings()->get('welcome_area')], 'replyToThread') }}"
               target="_blank"
               class="btn btn-accent">
                <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Create Post
            </a>
        </div>
    @else

        <p>Your division uses a welcome forum for all new recruit introductions. Click the button below to create a thread and introduce your new recruit to the other members of the division.</p>

        <div class="text-center p-lg">
            <a href="{{ doForumFunction([$division->settings()->get('welcome_area')], 'createThread') }}"
               target="_blank"
               class="btn btn-accent">
                <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Create Thread
            </a>
        </div>

    @endif

@else
    <p class="slight alert alert-warning">Your division is not configured or does not support a welcome area during recruitment.</p>
@endif

<hr />