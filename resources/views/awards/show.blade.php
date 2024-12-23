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

        {!! Breadcrumbs::render('awards.show', $award) !!}

        <div style="display:flex;align-items: center;justify-content: center;margin-top: 0;">
            <img src="{{ asset(Storage::url($award->image)) }}"
                 class="clan-award hidden-xs hidden-sm"
                 style="margin-right:50px;"
                 alt="{{ $award->name }}"
            />

            <div class="hidden-xs hidden-sm text-center">
                <h3>{{ $award->name }}</h3>
                <p style="max-width:500px;">{{ $award->description }}</p>
            </div>

            @if ($award->allow_request)
                <a href="#" data-toggle="modal" data-target="#award_modal"
                   title="Request this award for yourself or someone else"
                   style="margin-left:50px;"
                   class="btn btn-default hidden-xs hidden-sm">Request Award</a>
            @endif

        </div>
    </div>

    <div class="visible-xs visible-sm text-center">
        <img src="{{ asset(Storage::url($award->image)) }}"
             class="clan-award clan-award-zoom"
             alt="{{ $award->name }}"
        />
        <hr>
        <h3>{{ $award->name }}</h3>
        <p>{{ $award->description }}</p>
        @if ($award->allow_request)
            <a href="#" data-toggle="modal" data-target="#award_modal"
               title="Request this award for yourself or someone else"
               class="btn btn-default m-t-md">Request Award</a>
        @endif
    </div>

    <hr>

    @if ($award->recipients->count())
        <div class="panel panel-filled">
            <div class="panel-body">
                <h4 class="text-center text-uppercase">Award Recipients</h4>
                <table class="table table-hover basic-datatable">
                    <thead>
                    <tr>
                        <th class="text-center">Member</th>
                        <th class="text-center">Awarded on</th>
                    </tr>
                    </thead>
                    @foreach ($award->recipients as $record)
                        <tr>
                            <td class="text-center"><a href="{{ route('member', $record->member->getUrlParams()) }}">
                                    {{ $record->member->name }}
                                </a></td>
                            <td class="text-center">{{ $record->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

    @else
        <h3 class="text-muted text-center">No Recipients</h3>
        <p class="text-center">This award remains elusive. Perhaps you are up to the task?</p>
    @endif


    @include('awards.partials.award-form')

@endsection
