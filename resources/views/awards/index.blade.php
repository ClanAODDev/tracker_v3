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
        <h3>
            Achievements

            @if ($division = request('division'))
                - {{ ucwords($division) }}
            @endif
        </h3>
        <p>Stand out as a member of the community by earning one or more of the awards listed below.</p>

        <hr>
        <div class="row">
            @foreach ($awards->sortBy('display_order') as $award)

                <div class="col-xl-2 col-md-6">
                    <a class="panel panel-filled" href="{{ route('awards.show', $award) }}">
                        <div class="panel-body" title="{{ $award->description ?? "No description" }}"
                             style="height:160px; overflow: hidden">
                            <div class="col-xs-3"
                                 style="height:100%;display:flex;justify-content: center;align-items: center">
                                <img src="{{ asset(Storage::url($award->image)) }}"
                                     class="clan-award"
                                     alt="{{ $award->name }}"
                                />
                            </div>
                            <div class="col-xs-9 p-0 text-center"
                                 style="height:100%;display:flex;align-items: center;justify-content: center;">
                                <div>
                                    <span class="c-white">{{ $award->name }}</span>
                                    <p class="award-description">
                                        @if ($award->description)
                                            {{ $award->description }}
                                        @else
                                            <span class="text-muted">No description</span>
                                        @endif
                                    </p>

                                    <span class="award-metadata">{{ $award->recipients_count }}</span>

                                </div>

                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection