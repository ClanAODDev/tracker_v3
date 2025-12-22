@php
    $visibleTags = \App\Models\DivisionTag::forDivision($division->id)
        ->visibleTo()
        ->withCount(['members' => function ($query) use ($division) {
            $query->where('division_id', $division->id);
        }])
        ->get();
@endphp
<select id="tag-filter" multiple="multiple" placeholder="Filter by tag">
    @foreach($visibleTags as $tag)
        <option value="{{ $tag->id }}" data-count="{{ $tag->members_count }}">{{ $tag->name }} ({{ $tag->members_count }})</option>
    @endforeach
</select>
