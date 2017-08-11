@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Promotions Report</span>
            <span class="visible-xs">Promotions</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('promotions', $division) !!}

        @if (count($members))
            <div class="row">
                <div class="col-md-6">
                    @foreach ($members->groupBy('rank.name') as $rank=>$members)
                        <div class="panel m-b-none">
                            <div class="panel-body">
                                <table class="table table-condensed">
                                    <thead>
                                    <tr>
                                        <th>{{ $rank }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            <td>{{ $member->name }}</td>
                                            <td class="text-right">{{ $member->last_promoted }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td class="text-accent">Total</td>
                                        <td class="text-accent text-right">{{ $members->count() }}</td>
                                    </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    @endforeach

                </div>
            </div>


        @else
            <p>No promotions found.</p>
        @endif

    </div>
@stop
