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

            <div class="col-sm-8">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#notes-panel" aria-expanded="true"> Notes</a></li>
                        <li class=""><a data-toggle="tab" href="#division-data-panel" aria-expanded="false">Division Data</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="notes-panel" class="tab-pane active">
                            <div class="panel-body">
                                @include('member.partials.notes')
                            </div>
                        </div>
                        <div id="division-data-panel" class="tab-pane">
                            <div class="panel-body">
                                <strong class="c-white">Donec quam felis</strong>

                                <p>Thousand unknown plants are noticed by me: when I hear the buzz of the little world among the stalks, and grow familiar with the countless indescribable forms of the insects
                                    and flies, then I feel the presence of the Almighty, who formed us in his own image, and the breath </p>

                                <p>I am alone, and feel the charm of existence in this spot, which was created for the bliss of souls like mine. I am so happy, my dear friend, so absorbed in the exquisite
                                    sense of mere tranquil existence, that I neglect my talents. I should be incapable of drawing a single stroke at the present moment; and yet.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-sm-4">
                @include('member.partials.general-information')
                @include('member.partials.aliases')
                @include ('member.partials.part-time-divisions')
            </div>
        </div>

    </div>

@stop
