@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            <span>{{ $division->name }}</span>
            @include('division.partials.edit-division-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include('division.partials.tools-links')
        @include ('division.partials.pending-actions')
        @include('division.partials.quick-stats')
        @include ('division.partials.census-short')
        @include('division.partials.leadership')
        @include('division.partials.anniversaries')
        @include('division.partials.platoons')

    </div>

    @can('recruit', App\Models\Member::class)
        @if($division->settings()->get('application_required', false))
            @include('division.partials.applications-modal')
        @endif
    @endcan

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/division.js'])
@endsection
