@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member ) !!}

    <div class="row">
        <div class="col-xs-6">

            <h2>
                <strong>{{ $member->name }}</strong>
                <small>{{ $member->rank->name }}</small>
                <br/>

                <div class="btn-group">

                    <a class="btn btn-default btn-xs popup-link"
                       href="{{ Helpers::getAODPmUrl($member->clan_id) }}"
                       target="_blank"><i class="fa fa-comment"></i> Send PM</a>

                    <a class="btn btn-default btn-xs popup-link"
                       href="{{ Helpers::getAODEmailUrl($member->clan_id) }}"
                       target="_blank"><i class="fa fa-envelope"></i> Send Email</a>

                </div>
            </h2>
        </div>

        <div class="col-xs-6">

            <div class="btn-group pull-right" data-player-id="{{ $member->clan_id }}"
                 data-user-id="{{ $member->clan_id }}">
                <button type="button" class="btn btn-info edit-member"><i class="fa fa-pencil fa-lg"></i> Edit
                </button>

                {{-- if user sgt or higher, show remove button --}}
                @if ($member->rank_id >= 9)
                    <a href="#" title="Remove player from AOD" class="removeMember btn btn-danger"><i
                                class="fa fa-trash fa-lg"></i> Remove<span class="hidden-sm hidden-xs"> from AOD</span></a>

                @else
                    {{-- else show request removal--}}
                    <a href="#" title="Remove player from AOD" class="requestRemoval btn btn-warning"><i
                                class="fa fa-trash fa-lg"></i> Request<span class="hidden-sm hidden-xs"> removal</span></a>

                @endif
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
