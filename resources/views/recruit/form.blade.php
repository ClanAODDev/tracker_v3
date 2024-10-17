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

    <div class="container-fluid" id="recruiting-container">
        <recruiting-process division="{{ $division->slug }}"
                            recruiter_id="{{ auth()->user()->member->clan_id }}"
                            :ranks="{{ json_encode(\App\Enums\Rank::getAllRanks()) }}"
                            handle-name="{{ $division->handle->label ?? 'Ingame Name' }}"></recruiting-process>
    </div>
@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js?v=7.35') !!}"></script>
@endsection
