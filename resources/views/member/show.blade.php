@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.member-actions-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            @if ($member->isPending)
                <span class="text-accent"><i class="fa fa-hourglass"></i> Pending member</span>
            @elseif ($member->division_id == 0)
                <span class="text-muted"><i class="fa fa-user-times"></i> Ex-AOD</span>
            @else
                {{ $member->position?->getLabel() ?? "No Position" }}
            @endif
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

        @can('delete', $member)
            <hr>
            @if ($member->division)
                <form action="{{ route('deleteMember', [$member->clan_id]) }}" method="post">
                    @csrf
                    @method('delete')
                    <div class="panel panel-filled panel-c-danger collapsed">
                        <div class="panel-heading panel-toggle">
                            <div class="panel-tools">
                                <i class="fa fa-chevron-up toggle-icon"></i>
                            </div>
                            <i class="fa fa-trash text-danger"></i> Remove Member
                        </div>

                        <div class="panel-body">
                            <p>
                                <span class="text-warning">WARNING:</span> You are about to remove a member from AOD, which cannot be reversed. One removed, a member
                                <strong>MUST</strong> be re-inducted through the traditional recruitment procedure. This process does several things:
                            </p>
                            <ul>
                                <li>Resets any platoon, squad, position, and leadership assignments the member currently has</li>
                                <li>Dissociates the member from any division they are currently full-time, or part-time in</li>
                                <li>Opens the AOD Member Removal form, performing forum removal from AOD</li>
                            </ul>

                            <p>If you are sure you wish to proceed, provide a brief explanation for the removal, and click to proceed.</p>

                            <div class="form-group">
                                <label for="removal_reason">Reason</label>
                                <textarea name="removal_reason" id="removal_reason" rows="3" class="form-control" required="required"></textarea>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" title="Remove player from AOD" data-member-id="{{ $member->clan_id }}"
                                    class="btn btn-danger remove-member">Submit<span class="hidden-sm hidden-xs"> removal</span></button>
                        </div>
                    </div>
                </form>
            @endif
        @endcan

    </div>

@endsection
