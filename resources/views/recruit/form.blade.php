@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Add New Recruit
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div id="recruiting-container"
             data-division="{{ $division->slug }}"
             data-recruiter-id="{{ auth()->user()->member?->clan_id ?? auth()->user()->id }}"
             data-ranks="{{ json_encode(\App\Enums\Rank::getAllRanks()) }}"
             data-rank-labels="{{ json_encode(\App\Enums\Rank::getAllRanksWithLabels()) }}">
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/recruiting.js'])
@endsection
