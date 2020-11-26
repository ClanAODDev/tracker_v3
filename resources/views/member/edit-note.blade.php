@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                     class="division-icon-large" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.member-actions-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            {{ $member->position->name  }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member-note', $member, $division) !!}

        {!! Form::model($note, ['method' => 'post', 'route' => ['updateNote', $member->clan_id, $note]]) !!}
        @include ('member.forms.note-form', ['action' => 'Edit Member Note'])
        {!! Form::close() !!}

        @include('member.partials.note-feed')

        {!! Form::model($note, ['method' => 'delete', 'route' => ['deleteNote', $member->clan_id, $note]]) !!}
        @include ('member.forms.remove-note-form')
        {!! Form::close() !!}
    </div>

@endsection