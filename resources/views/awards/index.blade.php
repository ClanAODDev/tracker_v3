@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset(config('app.logo')) }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('awards.index') !!}

        <h3>
            Achievements

            @if ($division = request('division'))
                - {{ ucwords(str_replace('-', ' ', $division)) }}
            @endif
        </h3>
        <p>Stand out as a member of the community by earning one or more of the awards listed below.</p>

        <hr>
        <div class="row">
            @foreach ($awards->sortBy('display_order') as $award)

                <div class="col-xl-2 col-md-6">
                    <a class="panel panel-filled" href="{{ route('awards.show', $award) }}">
                        <div class="panel-body"
                             style="height:160px; overflow: hidden">
                            <div class="col-xs-3 award-image">
                                <img src="{{ asset(Storage::url($award->image)) }}"
                                     class="clan-award-zoom"
                                     alt="{{ $award->name }}"
                                />
                            </div>
                            <div class="col-xs-9 p-0 text-center award-display-meta">
                                <div>
                                    <span class="c-white">{{ $award->name }}</span>
                                    <p class="award-description">
                                        @if ($award->description)
                                            {{ $award->description }}
                                        @else
                                            <span class="text-muted">No description</span>
                                        @endif
                                    </p>

                                    <span class="award-metadata award-{{ $award->id }}">{{ $award->recipients_count }}</span>

                                </div>

                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
