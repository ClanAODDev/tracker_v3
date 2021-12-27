@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Leadership Training
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Training Module
        @endslot
    @endcomponent

    <div class="container-fluid" id="training-container">

        @include('application.partials.errors')

        <div class="row">
            <div class="col-md-12">
                <h4>SGT Training Process</h4>
                <hr>

                <nav class="tabs-container">
                    <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#sgt-duties" role="tab"><i class="fas fa-check-circle text-muted"></i> Sgt
                                Duties</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#sgt-structure" role="tab"><i class="fas fa-check-circle text-muted"></i> SGT
                                Structure</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#forum-mod" role="tab"><i class="fas fa-check-circle text-muted"></i> Forum
                                Mod</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#ts-mod" role="tab"><i class="fas fa-check-circle text-muted"></i> Teamspeak
                                Mod</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#misc" role="tab"><i class="fas fa-check-circle text-muted"></i> Misc
                                Info</a></li>
                    </ul>
                </nav>

                <div class="panel panel-filled">
                    <div class="tab-content" id="nav-tabContent">

                        <div class="tab-pane p-md fade" id="sgt-duties" role="tabpanel">
                            @include('training.partials.sgt.sgt-duties')
                        </div>

                        <div class="tab-pane fade p-md" id="sgt-structure" role="tabpanel">
                            @include('training.partials.sgt.sgt-structure')
                        </div>
                        <div class="tab-pane fade p-md" id="forum-mod" role="tabpanel">
                            @include('training.partials.sgt.forum-moderation')
                        </div>

                        <div class="tab-pane fade p-md" id="ts-mod" role="tabpanel">
                            @include('training.partials.sgt.ts-moderation')
                        </div>
                        <div class="tab-pane fade p-md" id="misc" role="tabpanel">
                            @include('training.partials.sgt.misc-info')
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6">
                        <h4>SGT Handbook (in revision)</h4>
                        <p>The current SGT handbook has been deemed deprecated, and a new forum based version is
                            being prepared. This resource will be updated once the newer version is available</p>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-filled {{ $errors->has('clan_id') ? 'panel-c-danger' : null }}">
                            <div class="panel-heading">Confirm Training</div>
                            <div class="panel-body">
                                <p>Once you are finished with the training session, enter the clan id for the SGT you
                                    are training and submit.</p>
                                <p>This will update the member's last training date, and set you as the trainer.</p>
                            </div>
                            <div class="panel-footer">

                                <form action="{{ route('training.update') }}" method="POST">
                                    {{ csrf_field() }}
                                    <table class="table table-bordered">
                                        <tr>
                                            <td>
                                                <input type="number"
                                                       class="form-control col-9"
                                                       id="clan_id"
                                                       name="clan_id"
                                                       placeholder="Enter Clan ID..." value="{{ old('clan_id') ?? $_GET['clan_id'] }}"></td>
                                            <td>
                                                <button type="submit" class="btn btn-default btn-block">Submit</button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('footer_scripts')
    <script>
        $('.nav-item').click(function () {
            $(this).find('.fa-check-circle').removeClass('text-muted').addClass('text-success');
        })
    </script>
@endsection