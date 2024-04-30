@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Add New Recruit
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid" id="recruiting-container">
        <recruiting-process division="{{ $division->abbreviation }}"
                            recruiter_id="{{ auth()->user()->member->clan_id }}"
                            :ranks="{{ \App\Models\Rank::all()->pluck('abbreviation', 'id') }}"
                            handle-name="{{ $division->handle->label ?? 'Ingame Name' }}"></recruiting-process>
    </div>
@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js?v=7.32') !!}"></script>
@endsection