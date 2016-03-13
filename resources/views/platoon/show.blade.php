@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoons', $platoon->division, $platoon) !!}

    <h1>
        <img src="/images/game_icons/48x48/{{ $platoon->division->abbreviation }}.png"/>
        <strong>{{ $platoon->name }}</strong>
    </h1>

    <hr/>

    <div class="row">

        <div class="col-md-8">

            <div class="panel panel-primary">
                <div class="panel-heading">Members</div>
                <div class="list-group">
                    @foreach($platoon->members as $member)
                        <li class="list-group-item">
                            {{ $member->name }}
                        </li>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

@stop