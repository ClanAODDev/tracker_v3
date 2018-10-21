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
                <p>Interested in attending the <span
                            class="text-accent">AOD US Las Vegas 2019</span> meetup? Hit the button below so we know you're planning on taking part.
                </p>
                <p>Information about the AOD US Meetup will be distributed as the event gets closer. Details regarding the event are expected to be kept confidential.</p>

                @unless($optedIn)

                    <form action="{{ url('vegas/opt-in') }}" method="post">
                        {{ csrf_field() }}
                        <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Opt In</button>
                    </form>
                @else
                    <form action="{{ url('vegas/opt-out') }}" method="post">
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
