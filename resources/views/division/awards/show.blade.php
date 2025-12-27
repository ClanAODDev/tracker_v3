@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            Awards
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-medal"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            {{ $award->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('awards.show', $award) !!}

        <div class="row m-b-lg">
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $stats->total }}</h1>
                        <div class="text-muted">Total Recipients</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-info">{{ $stats->firstAwarded?->format('M Y') ?? '-' }}</span>
                        </h1>
                        <div class="text-muted">First Awarded</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-success">{{ $stats->lastAwarded?->format('M Y') ?? '-' }}</span>
                        </h1>
                        <div class="text-muted">Most Recent</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            @if ($award->allow_request)
                                <span class="text-success"><i class="fa fa-check"></i></span>
                            @else
                                <span class="text-muted"><i class="fa fa-times"></i></span>
                            @endif
                        </h1>
                        <div class="text-muted">Requestable</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-filled award-detail award-detail-{{ $stats->rarity }}" style="position: relative; overflow: hidden;">
            <div class="rarity-indicator rarity-{{ $stats->rarity }}"></div>
            <div class="panel-body">
                <div style="display:flex;align-items: center;justify-content: center;">
                    <div class="award-image-wrapper-lg hidden-xs hidden-sm" style="margin-right:50px;">
                        @if($award->image && Storage::disk('public')->exists($award->image))
                            <img src="{{ $award->getImagePath() }}"
                                 class="clan-award clan-award-zoom"
                                 alt="{{ $award->name }}"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                            />
                            <div class="award-placeholder-lg" style="display:none">
                                <i class="fas fa-trophy"></i>
                            </div>
                        @else
                            <div class="award-placeholder-lg">
                                <i class="fas fa-trophy"></i>
                            </div>
                        @endif
                    </div>

                    <div class="hidden-xs hidden-sm text-center">
                        <h3>{{ $award->name }}</h3>
                        <span class="award-pill pill-{{ $stats->rarity }}" style="margin-left: 5px;">{{ ucfirst($stats->rarity) }}</span>
                        <p style="max-width:500px; margin-top: 15px;">{{ $award->description }}</p>
                    </div>

                    @if ($award->allow_request && ($award->division?->active ?? true))
                        <a href="#" data-toggle="modal" data-target="#award_modal"
                           title="Request this award for yourself or someone else"
                           style="margin-left:50px;"
                           class="btn btn-default hidden-xs hidden-sm">Request Award</a>
                    @endif
                </div>

                <div class="visible-xs visible-sm text-center">
                    <div class="award-image-wrapper-lg" style="display:inline-flex;margin-bottom:15px;">
                        @if($award->image && Storage::disk('public')->exists($award->image))
                            <img src="{{ $award->getImagePath() }}"
                                 class="clan-award"
                                 alt="{{ $award->name }}"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                            />
                            <div class="award-placeholder-lg" style="display:none">
                                <i class="fas fa-trophy"></i>
                            </div>
                        @else
                            <div class="award-placeholder-lg">
                                <i class="fas fa-trophy"></i>
                            </div>
                        @endif
                    </div>
                    <h3>{{ $award->name }}</h3>
                    @if ($award->division)
                        <span class="label label-default">{{ $award->division->name }}</span>
                        @unless($award->division->active)
                            <span class="label label-warning">Legacy</span>
                        @endunless
                    @else
                        <span class="label label-warning">Clan-Wide</span>
                    @endif
                    <span class="award-pill pill-{{ $stats->rarity }}">{{ ucfirst($stats->rarity) }}</span>
                    <p>{{ $award->description }}</p>
                    @if ($award->allow_request && ($award->division?->active ?? true))
                        <a href="#" data-toggle="modal" data-target="#award_modal"
                           title="Request this award for yourself or someone else"
                           class="btn btn-default m-t-md">Request Award</a>
                    @endif
                </div>
            </div>
        </div>

        @if ($recipients->count())
            <div class="panel panel-filled">
                <div class="panel-heading">
                    Award Recipients
                    <span class="badge pull-right">{{ $recipients->total() }}</span>
                </div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Member</th>
                            <th class="text-center hidden-xs">Division</th>
                            <th class="text-center hidden-xs">Rank</th>
                            <th class="text-center">Awarded</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($recipients as $record)
                            <tr>
                                <td>
                                    @if ($record->member)
                                        <a href="{{ route('member', $record->member->getUrlParams()) }}">
                                            {{ $record->member->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Unknown Member</span>
                                    @endif
                                </td>
                                <td class="text-center hidden-xs">
                                    @if ($record->member?->division)
                                        <a href="{{ route('division', $record->member->division) }}">
                                            {{ $record->member->division->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center hidden-xs">
                                    @if ($record->member)
                                        {{ $record->member->rank->getAbbreviation() }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $record->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($recipients->hasPages())
                    <div class="panel-footer text-center">
                        {{ $recipients->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="panel panel-filled">
                <div class="panel-body text-center">
                    <h3 class="text-muted">No Recipients</h3>
                    <p class="text-muted">This award remains elusive. Perhaps you are up to the task?</p>
                </div>
            </div>
        @endif
    </div>

    @include('division.awards.partials.award-form')

@endsection

@if($award->allow_request && ($award->division?->active ?? true))
@section('footer_scripts')
<script>
$(function() {
    $('#request-for-self').on('click', function() {
        $('#member').val('{{ auth()->user()->member->name ?? '' }}');
        $('#member_id').val('{{ auth()->user()->member->clan_id ?? '' }}');
    });

    $('#award-request-form').on('submit', function() {
        var $btn = $('#award-submit-btn');
        $btn.prop('disabled', true).html('<span class="themed-spinner spinner-sm"></span> Submitting...');
    });
});
</script>
@endsection
@endif
