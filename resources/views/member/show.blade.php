@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('members', $member->primaryDivision(), $member->platoon, $member ) !!}

    <div class="row">
        <div class="col-xs-6">

            <h2>
                <strong>{{ $member->name }}</strong>
                <small>{{ $member->rank->name }}</small>
                <br/>

                <div class="btn-group">

                    <a class="btn btn-default btn-xs popup-link"
                       href="http://www.clanaod.net/forums/private.php?do=newpm&amp;u=31832&amp;url=http://www.clanaod.net/forums/member.php?u=31832"
                       target="_blank"><i class="fa fa-comment"></i> Send PM</a>

                    <a class="btn btn-default btn-xs popup-link"
                       href="http://www.clanaod.net/forums/sendmessage.php?do=mailmember&amp;u=31832&amp;url=http://www.clanaod.net/forums/member.php?u=31832"
                       target="_blank"><i class="fa fa-envelope"></i> Send Email</a>

                </div>

            </h2>
        </div>

        <div class="col-xs-6">

            {{--Implement authorization here--}}
            <div class="btn-group pull-right" data-player-id="31832" data-user-id="31832">
                <button type="button" class="btn btn-info edit-member"><i class="fa fa-pencil fa-lg"></i> Edit</button>
                <a href="#"
                   title="Remove player from AOD"
                   class="removeMember btn btn-danger"><i class="fa fa-trash fa-lg"></i> Remove<span
                            class="hidden-sm hidden-xs"> from AOD</span></a>
            </div>

        </div>

    </div>

    <hr/>

@stop