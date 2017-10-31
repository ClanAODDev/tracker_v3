@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Member Retention
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('retention-report', $division) !!}

        <p>This report provides recruiting and removal information. Only recruits done for the current division are included.</p>

        <hr />



        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        Recruiting Count
                        <span class="pull-right">{{ $totalRecruitCount }}</span>
                    </div>
                    @foreach ($members as $item)
                        <li class="list-group-item">
                            <a href="{{ route('member', $item['member']->getUrlParams()) }}">
                                {{ $item['member']->present()->rankName }}
                            </a>
                            <span class="pull-right badge">{{ $item['recruits'] }}</span>
                        </li>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>
    </div>

@stop