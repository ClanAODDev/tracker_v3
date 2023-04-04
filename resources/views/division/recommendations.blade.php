@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
        @slot ('heading')
            {{ $division->name }}
        @endslot
        @slot ('subheading')
            Recommendations
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('recommendations', $division) !!}

        <hr/>

        @foreach ($recommendations as $recommendation)
            @dump($recommendation->toArray()) <br />
        @endforeach

        {{-- @include('division.partials.recommendations-log')--}}
    </div>

@endsection
