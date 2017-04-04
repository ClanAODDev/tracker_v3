@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.edit-member-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            {{ $member->position->name  }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member', $member) !!}

        <div class="row">

            <div class="col-sm-4">
                <h4>Member Aliases</h4>
                @forelse ($member->handles as $handle)
                    <div class="panel panel-filled">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-6">
                                    {{ $handle->pivot->value }}
                                </div>
                                <div class="col-xs-6 m-t-xs">
                                    <small class="text-muted slight pull-right text-right">
                                        {{ $handle->name }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="panel panel-filled">
                        <div class="panel-body">
                            <img src="{{ asset('images/logo_v2.svg') }}" class="division-icon-small" />
                            No aliases recorded
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- general information --}}
            <div class="col-sm-4 pull-right">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        <strong>Member Info</strong>
                    </div>
                    <table class="table small">
                        <tbody>
                        <tr>
                            <td>
                                <span class="c-white">Last active</span> <span
                                        class="pull-right">{{ $member->last_activity->diffForHumans() }}</span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <span class="c-white">In Teamspeak</span> <span
                                        class="pull-right">{{ $member->last_activity->diffForHumans() }}</span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <span class="c-white">Joined</span> <span
                                        class="pull-right">{{ $member->join_date }}</span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <span class="c-white">Last promoted</span> <span
                                        class="pull-right">{{ $member->last_promoted }}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                @include ('member.partials.part-time-divisions')

            </div>
        </div>

    </div>

@stop
