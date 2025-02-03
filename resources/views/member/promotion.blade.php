@extends('application.base-tracker')

@section('content')

    <div class="container-center md">

            <div class="container-center md">

                <div class="view-header">
                    <div class="header-icon">
                        <i class="pe page-header-icon pe-7s-add-user"></i>
                    </div>
                    <div class="header-title mb-3">
                        <h3>Congratulations</h3>
                        <small>
                             Promotion pending for {{ $member->name }}
                        </small>
                    </div>
                </div>

                <div class="panel panel-filled m-t-md">
                    <div class="panel-body m-b-n p-0">
                        <p>The Angels of Death congratulate you on your achievements and contributions to the
                            community. Please indicate whether you choose to accept the rank of:</p>
                        <p class="c-white text-uppercase"><strong>{{ $action->rank->getLabel() }}</strong></p>
                        <p class="text-muted">This page will expire {{ $expirationTime }}.</p>
                    </div>
                </div>
                <div class="pull-left">
                    <form action="{{ route('promotion.decline', [$member->clan_id, $action]) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-default"
                                onclick="confirm('By declining this promotion, you agree to remain at your current rank. ' +
                        'Press OK to continue...')">Decline Promotion</button>
                    </form>
                </div>

                <div class="pull-right">
                    <a href="#" class="btn btn-success">Accept Promotion</a>
                </div>

            </div>
    </div>

@endsection

