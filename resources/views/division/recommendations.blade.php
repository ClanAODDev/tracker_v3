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

        <strong class="c-white text-uppercase">Recommendations for rank</strong>
        <hr>
        @if($recommendations->count())
            <div class="list-group">

                @foreach ($recommendations as $recommendation)
                    <div class="list-group-item d-flex justify-content-between">
                        <span><code>{{ $recommendation->member->name }}</code>
                        recommended for
                        @if ($recommendation->isPromotion())
                                <strong class="text-success">PROMOTION</strong>
                            @else
                                <strong class="text-danger">DEMOTION</strong>
                            @endif
                    </span>
                        <span class="pull-right"><i class="fa fa-clock"></i> {{ $recommendation->effective_at->format('M d, Y') }}</span>
                    </div>
                @endforeach
            </div>

        @else
            <div class="text-center">
                <p><strong>No recommendations available</strong></p>
                <p>Visit a <a href="{{ route('division.members', $division) }}">member profile</a> to make
                    recommendations for
                    rank
                    .</p>
            </div>
        @endif



        {{-- @include('division.partials.recommendations-log')--}}
    </div>

@endsection
