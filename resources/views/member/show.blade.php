@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            <div class="member-header-info">
                <span class="member-name">{!! $member->present()->rankName !!}</span>
                <span class="member-position">
                    @if ($member->isPending)
                        <span class="text-accent"><i class="fa fa-hourglass"></i> Pending</span>
                    @elseif ($member->division_id == 0)
                        <i class="fa fa-user-times"></i> Ex-AOD
                    @else
                        {{ $member->position?->getLabel() ?? "No Position" }}
                    @endif
                </span>
                @include('member.partials.member-actions-button', ['member' => $member])
            </div>
        @endslot
        @slot ('subheading')
            @php
                $user = auth()->user();
                $visibleTags = $member->tags->filter(function ($tag) use ($user) {
                    if (!$user) {
                        return $tag->visibility === \App\Enums\TagVisibility::PUBLIC;
                    }
                    if ($user->isRole(['admin', 'sr_ldr'])) {
                        return true;
                    }
                    if ($user->isRole('officer')) {
                        return in_array($tag->visibility, [\App\Enums\TagVisibility::PUBLIC, \App\Enums\TagVisibility::OFFICERS]);
                    }
                    return $tag->visibility === \App\Enums\TagVisibility::PUBLIC;
                });
            @endphp
            <div class="member-tags" id="member-tags-display"
                 @can('assign', [App\Models\DivisionTag::class, $member])
                     data-tags-url="{{ route('member-tags.get', [$division, $member->clan_id]) }}"
                 data-add-url="{{ route('member-tags.add', [$division, $member->clan_id]) }}"
                 data-remove-url="{{ route('member-tags.remove', [$division, $member->clan_id]) }}"
                 data-create-url="{{ route('member-tags.create', [$division, $member->clan_id]) }}"
                    @endcan
            >
                @foreach ($visibleTags as $tag)
                    <span class="badge member-tag tag-visibility-{{ $tag->visibility->value }}"
                          title="{{ $tag->division->name }}" data-tag-id="{{ $tag->id }}"
                          data-visibility="{{ $tag->visibility->value }}">
                        {{ $tag->name }}
                        @can('assign', [App\Models\DivisionTag::class, $member])
                            <span class="remove-tag"
                                  style="margin-left: 4px; cursor: pointer; opacity: 0;">&times;</span>
                        @endcan
                    </span>
                @endforeach
                @can('assign', [App\Models\DivisionTag::class, $member])
                    <div class="dropdown" style="display: inline-block;">
                        <a href="#" class="badge badge-default" title="Add tag" data-toggle="dropdown">
                            <i class="fa fa-plus"></i>
                        </a>
                        <ul class="dropdown-menu" id="available-tags-dropdown">
                            <li class="dropdown-header">Loading...</li>
                        </ul>
                    </div>
                @endcan
            </div>
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member', $member, $division) !!}

        @include ('member.partials.notices')

        <div class="row m-b-xl">
            <div class="col-md-12">
                @include ('member.partials.general-information')
            </div>
        </div>

        @include ('member.partials.awards')
        @include ('member.partials.handles')
        @include ('member.partials.part-time-divisions')
        @include ('member.partials.member-history')

        @can('create', \App\Models\Note::class)
            @include ('member.partials.notes')
        @endcan

    </div>

    @can('create', App\Models\DivisionTag::class)
        @php
            $isSeniorLeader = auth()->user()->isRole(['admin', 'sr_ldr']);
        @endphp
        <div class="modal fade" id="create-tag-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Create Tag</h4>
                    </div>
                    <form id="create-tag-form">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="new-tag-name">Tag Name</label>
                                <input type="text" id="new-tag-name" name="name" class="form-control"
                                       placeholder="Enter tag name" maxlength="50" required>
                            </div>
                            <div class="form-group">
                                <label for="new-tag-visibility">Visibility</label>
                                <select id="new-tag-visibility" name="visibility" class="form-control">
                                    <option value="public">Public</option>
                                    <option value="leadership">Leadership Only</option>
                                    @if($isSeniorLeader)
                                        <option value="senior_leader">Senior Leader Only</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-accent">Create & Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

@endsection

@section('footer_scripts')
    <script src="{{ asset('js/member-tags.js') }}"></script>
@endsection
