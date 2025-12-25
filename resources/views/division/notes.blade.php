@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
{{ $division->name }}
            @include('division.partials.edit-division-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            Member Notes
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('division-notes', $division) !!}

        <div class="notes-toolbar">
            <div class="notes-filters">
                <a href="{{ route('division.notes', $division->slug) }}"
                   class="notes-filter-btn {{ !$type ? 'active' : '' }}">
                    <i class="fa fa-list"></i>
                    <span>All</span>
                </a>
                @foreach ($noteTypes as $key => $label)
                    <a href="{{ route('division.notes', $division->slug) }}?type={{ $key }}"
                       class="notes-filter-btn notes-filter-{{ $key }} {{ $type === $key ? 'active' : '' }}">
                        @if ($key === 'positive')
                            <i class="fa fa-thumbs-up"></i>
                        @elseif ($key === 'negative')
                            <i class="fa fa-thumbs-down"></i>
                        @elseif ($key === 'sr_ldr')
                            <i class="fa fa-lock"></i>
                        @else
                            <i class="fa fa-sticky-note"></i>
                        @endif
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>

            <form action="{{ route('division.notes', $division->slug) }}" method="GET" class="notes-search-form">
                @if ($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <div class="notes-search-wrapper">
                    <i class="fa fa-search notes-search-icon"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by member or content..."
                           class="notes-search-input">
                    @if ($search)
                        <a href="{{ route('division.notes', $division->slug) }}{{ $type ? '?type='.$type : '' }}"
                           class="notes-search-clear">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="notes-header">
            <h3 class="notes-title">
                {{ $type ? ($noteTypes[$type] ?? ucfirst($type)) : 'All Notes' }}
                <span class="notes-count">({{ count($notes) }})</span>
            </h3>
            @if ($search)
                <span class="notes-search-term">
                    Results for "{{ $search }}"
                </span>
            @endif
        </div>

        <div class="notes-list">
            @forelse ($notes as $note)
                <a href="{{ route('member', $note->member->getUrlParams()) }}" class="note-card note-{{ $note->type }}">
                    <div class="note-icon">
                        @if ($note->type === 'positive')
                            <i class="fa fa-thumbs-up"></i>
                        @elseif ($note->type === 'negative')
                            <i class="fa fa-thumbs-down"></i>
                        @elseif ($note->type === 'sr_ldr')
                            <i class="fa fa-lock"></i>
                        @else
                            <i class="fa fa-sticky-note"></i>
                        @endif
                    </div>
                    <div class="note-content">
                        <div class="note-body">{{ $note->body }}</div>
                        <div class="note-footer">
                            <div class="note-author">
                                <strong>{{ $note->member->present()->rankName }}</strong>
                                <span class="text-muted">by {{ $note->author?->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="note-meta">
                                <span class="note-time">{{ $note->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="notes-empty">
                    <i class="fa fa-file-text-o"></i>
                    <h4>No notes found</h4>
                    <p>
                        @if ($search)
                            No notes match "{{ $search }}"
                        @elseif ($type)
                            No {{ strtolower($noteTypes[$type] ?? $type) }} notes available
                        @else
                            No notes have been recorded yet
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/division.js'])
@endsection
