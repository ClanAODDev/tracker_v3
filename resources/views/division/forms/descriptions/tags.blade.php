<h4>Division Tags</h4>
<p>In addition to the default tags, divisions can define specific custom tags for leadership to use when annotating member profiles.</p>

<p>Deleted tags that are currently in use on a member note will be removed from that note.</p>

<h5 class="m-t-lg">Default Tags</h5>
@foreach ($defaultTags as $tag)
    <span class="badge">{{ $tag->name }}</span>
@endforeach