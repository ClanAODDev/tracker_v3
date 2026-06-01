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
                    <p class="text-uppercase" style="color:{{ $action->rank->getColorHex() }};font-size:18px;letter-spacing:.5px;margin-bottom:14px;">
                        <strong>{{ $action->rank->getLabel() }}</strong>
                    </p>

                    @if(in_array($action->rank, [\App\Enums\Rank::TRAINER, \App\Enums\Rank::LANCE_CORPORAL]))
                        <p>Welcome to the officer team. The effort and dedication you've shown to get here is recognized
                            and appreciated — we're glad to have you.</p>

                        <div style="border:1px solid rgba(255,255,255,0.08);border-left:3px solid var(--color-accent);border-radius:6px;padding:16px 20px;margin:16px 0;background:rgba(0,0,0,0.15);">
                            <p class="c-white m-b-sm"><strong>As an AOD officer, the following is expected of you at a minimum:</strong></p>
                            <ul style="list-style:none;padding:0;margin:0 0 14px;">
                                <li style="display:flex;align-items:flex-start;gap:10px;padding:5px 0;">
                                    <i class="fa fa-check" style="color:var(--color-success);margin-top:3px;flex-shrink:0;"></i>
                                    <span>Complete officer tasks as assigned, including inactivity checks at the required interval</span>
                                </li>
                                <li style="display:flex;align-items:flex-start;gap:10px;padding:5px 0;">
                                    <i class="fa fa-check" style="color:var(--color-success);margin-top:3px;flex-shrink:0;"></i>
                                    <span>Assist with planning, running, and participating in division events</span>
                                </li>
                                <li style="display:flex;align-items:flex-start;gap:10px;padding:5px 0;">
                                    <i class="fa fa-check" style="color:var(--color-success);margin-top:3px;flex-shrink:0;"></i>
                                    <span>Be present and active in your channels — your visibility matters</span>
                                </li>
                                <li style="display:flex;align-items:flex-start;gap:10px;padding:5px 0;">
                                    <i class="fa fa-check" style="color:var(--color-success);margin-top:3px;flex-shrink:0;"></i>
                                    <span>Be inclusive — if you see a new member or someone from another division playing alone, engage them and make an introduction</span>
                                </li>
                                <li style="display:flex;align-items:flex-start;gap:10px;padding:5px 0;">
                                    <i class="fa fa-check" style="color:var(--color-success);margin-top:3px;flex-shrink:0;"></i>
                                    <span>Lead by example: win with grace, lose with grace, and don't rage quit</span>
                                </li>
                            </ul>
                            <p class="text-muted m-b-n" style="font-size:13px;">
                                <i class="fa fa-info-circle m-r-xs"></i>
                                Reach out to your Trainer for division-specific guidance on officer duties within your division.
                            </p>
                        </div>

                        <p class="text-warning"><strong>By accepting this promotion, you are agreeing to uphold these
                                responsibilities.</strong></p>
                    @elseif($action->rank->value >= \App\Enums\Rank::SERGEANT->value)
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

