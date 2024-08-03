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

        <form action="{{ route('updateNote', [$member->clan_id, $note]) }}" method="post">
            @csrf
            @include ('member.forms.edit-note-form', ['action' => 'Edit Member Note'])
        </form>

        @include('member.partials.note-feed')

        <form action="{{ route('deleteNote', [$member->clan_id, $note]) }}" method="post">
            {{ method_field('DELETE') }}
            @csrf
            @include ('member.forms.remove-note-form')
        </form>
    </div>

@endsection