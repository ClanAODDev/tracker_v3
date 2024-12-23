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
                <div>
                    <a href="#" data-toggle="modal" data-target="#award_modal"
                       title="Request this award for yourself or someone else"
                       style="margin-left:50px;"
                       class="btn btn-default">Request Award</a>
                </div>
            @endif

        </div>
    </div>

    <div class="visible-xs visible-sm text-center">
        <img src="{{ asset(Storage::url($award->image)) }}"
             class="clan-award"
             alt="{{ $award->name }}"
        />
        <hr>
        <h3>{{ $award->name }}</h3>
        <p>{{ $award->description }}</p>

    </div>

    <hr>

    <h4>Award Recipients</h4>


    <table class="table table-hover basic-datatable">
        <thead>
        <tr>
            <th>Member</th>
            <th class="text-right">Awarded on</th>
        </tr>
        </thead>
        @foreach ($award->recipients as $record)
            <tr>
                <td><a href="{{ route('member', $record->member->getUrlParams()) }}">
                        {{ $record->member->name }}
                    </a></td>
                <td class="text-right">{{ $record->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </table>

    @include('awards.partials.award-form')

@endsection
