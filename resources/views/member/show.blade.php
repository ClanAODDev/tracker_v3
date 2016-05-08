@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member ) !!}

    <div class="row">
        <div class="col-xs-6">

            <h2>
                <strong>{!! $member->rankName !!}</strong>

                @if ($member->position)
                    <small>{{ $member->position->name }}</small>
                @endif

            </h2>
        </div>

        <div class="col-xs-6">

            <div class="btn-group pull-right" data-player-id="{{ $member->clan_id }}"
                 data-user-id="{{ $member->clan_id }}">
                <button type="button" class="btn btn-primary edit-member"><i class="fa fa-pencil fa-lg"></i> Edit
                </button>

                <button type="button" class="btn btn-primary"><i class="fa fa-comment"></i> Contact</button>

                {{-- <a class="btn btn-default btn-xs popup-link"
                       href="{{ Helpers::doForumFunction([$member->clan_id], 'pm') }}"
                       target="_blank"><i class="fa fa-comment"></i> Send PM</a>

                    <a class="btn btn-default btn-xs popup-link"
                       href="{{ Helpers::doForumFunction([$member->clan_id], 'email') }}"
                       target="_blank"><i class="fa fa-envelope"></i> Send Email</a>--}}

            </div>

            {{--Else show request removal button--}}
        </div>
    </div>
    <hr/>

    {{-- Member not primary in any division --}}
    @if (!$member->primaryDivision)
        <div class="alert alert-danger">This player does not belong to a division supported by the tracker.</div>
    @endif


@stop
