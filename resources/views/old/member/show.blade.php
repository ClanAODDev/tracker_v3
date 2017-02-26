@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member) !!}

    <div class="row">
        <div class="col-xs-6">

            <h2>
                <strong>{!! $member->present()->rankName !!}</strong>
                @if ($member->position)
                    <small>{{ $member->position->name }}</small>
                @endif
            </h2>
        </div>

        <div class="col-xs-6">

            <div class="btn-group pull-right">

                @can('update', $member)
                    <a href="{{ route('editMember', $member->clan_id) }}"
                       type="button" class="btn btn-default edit-member"><i class="fa fa-pencil fa-lg"></i> Edit
                    </a>
                @endcan

                <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-comment"></i> Contact
                    <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#">Send Forum PM</a></li>
                    <li><a href="#">Send Forum Email</a></li>
                </ul>
            </div>
        </div>
    </div>
    <hr/>

    {{-- Member not primary in any division --}}
    @if ( ! $member->primaryDivision)
        <div class="alert alert-danger">This player is no longer active in AOD.</div>
    @endif


@stop
