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

                    @if($action->rank->value >= \App\Enums\Rank::SERGEANT->value)
                        <p>Your promotion will take effect immediately after accepting. However, additional training
                            is required before full permissions are granted. Seek assistance from division or clan
                            leadership on next steps.
                        </p>
                    @endif
                    <p class="text-muted">This page will expire {{ $expirationTime }}.</p>
                </div>
            </div>
            <div class="pull-left">
                <form action="{{ route('promotion.decline', [$member->clan_id, $action]) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-default"
                            onclick="confirm('By declining this promotion, you agree to remain at your current rank. ' +
                        'Press OK to continue...')">Decline Promotion
                    </button>
                </form>
            </div>

            <div class="pull-right">
                <form action="{{ route('promotion.accept', [$member->clan_id, $action]) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-success"
                            onclick="confirm('Upon acceptance, your forum rank will be updated automatically. ' +
                        'Press OK to continue...')">Accept Promotion
                    </button>
                </form>
            </div>

        </div>
    </div>

@endsection

