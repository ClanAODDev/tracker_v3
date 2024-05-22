@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
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