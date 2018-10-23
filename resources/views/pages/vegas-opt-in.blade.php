@extends('application.base')

@section('content')

    <div class="container-center md">
        @component('application.components.view-heading')
            @slot('heading')
                AOD US 2019 Meetup
            @endslot
            @slot('subheading')
                Fall 2019 - Las Vegas, NV
            @endslot
            @slot('icon')
                <i class="pe page-header-icon pe-7s-joy"></i>
            @endslot
            @slot('currentPage')
                v3
            @endslot
        @endcomponent

        <div class="panel panel-filled">
            <div class="panel-body">
                <p>Seriously considering going to the <strong class="text-info">AOD Live meetup</strong> in Las Vegas, Nevada planned for the fall of 2019? Use the button below to get additional details as they come available.
                </p>
                <p>Planning discussions and information and about the AOD Live meetup will only include those who are opted in. Expect more detail to be shared as the event gets closer. Our goal is to give you enough information to plan thoroughly without compromising the event by sharing broadly.</p>

                <p>If things change and you cannot attend, tell us by opting out. Once again, please keep details of AOD Live events strictly confidential to members you know are planning to attend and family who may join you.
                </p>

                <p>For questions, contact <a href="{{ doForumFunction(['20755'], 'pm') }}" class="text-accent">AOD_Silencer77</a> via forum message.
                </p>

                <hr>

                @unless($optedIn)

                    <form action="{{ url('vegas2019/opt-in') }}" method="post">
                        {{ csrf_field() }}
                        <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Opt In</button>
                    </form>
                @else
                    <form action="{{ url('vegas2019/opt-out') }}" method="post">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <span class="btn btn-success mr-3" style="pointer-events: none"><i
                                    class="fa fa-check text-success"></i> You're in!</span>
                        <button class="btn btn-danger pull-right" type="submit"><i
                                    class="fa fa-times text-danger"></i> Opt Out
                        </button>
                    </form>

                @endunless

            </div>
        </div>
    </div>

@endsection
