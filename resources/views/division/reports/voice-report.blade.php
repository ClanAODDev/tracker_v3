@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
        @slot ('heading')
            Voice Report
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('voice-report', $division) !!}

        @if (count($issues))
            <h4 class="m-t-xl">
                <i class="fa fa-exclamation-triangle text-danger"></i> Affected members
                <span class="pull-right text-muted">{{ count($issues) }} Issues</span>
            </h4>
            <hr/>

            <table class="table table-hover adv-datatable">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>State</th>
                    <th>{{ $division->locality('platoon') }}</th>
                    <th>Last Activity</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($issues as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                            {{ $member->present()->rankName }}
                        </td>
                        <td>
                            @include('division.partials.voice-status', ['status' =>  $member->last_voice_status])
                        </td>
                        <td>
                            {{ $member->platoon->name }}
                        </td>
                        <td>
                            {{-- temporary handling of null dates --}}
                            @if(str_contains($member->last_voice_activity, '1970'))
                                Never
                            @else
                                {{ $member->last_voice_activity }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>Congratulations, you have no discord issues!</p>
        @endif
    </div>

    <div class="panel panel-filled m-t-xl">
        <div class="panel-heading">
            Possible States
        </div>
        <div class="panel-body">
               <p><strong class="text-danger">Disconnected</strong>: User was connected but not anymore.</p>
               <p> <strong class="text-muted">Never Connected</strong>: User has never connected to the AOD Discord.</p>
               <p> <strong class="text-warning">Never Configured</strong>: User has not provided Discord information to AOD.</p>
        </div>
    </div>

@endsection