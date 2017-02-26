@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('part-timers', $division) !!}

    <h2>
        @include('division.partials.icon')
        <strong>{{ $division->name }}</strong>
        <small>Part-time Members</small>
    </h2>
    <hr/>

    @forelse ($partTime as $member)
        <div class="list-group-item">
            {{ $member->present()->rankName }}
            <span class="text-muted pull-right clearfix">{{ $member->primaryDivision->name }}</span>
        </div>
    @empty
        <p>This division has no part-time members.</p>
    @endforelse

@stop