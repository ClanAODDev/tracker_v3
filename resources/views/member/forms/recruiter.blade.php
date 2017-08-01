<div class="division-header">
    <div class="header-icon">
        @if ($member->recruiter)
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        @else
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
        @endif
    </div>
    <div class="header-title">
        <h4 class="m-b-xs">
            @if ($member->recruiter)
                {{ $member->recruiter->present()->rankName }}
            @else
                No Recruiter
            @endif
        </h4>
        <small class="slight">
            @if ($member->recruiter)
                {{ $member->recruiter->division->name }}
            @endif
        </small>
    </div>
    <hr />
</div>

<div class="row">
    <div class="col-md-6">
        <input type="text" class="form-control">
    </div>
    <div class="col-md-6">
        <input type="text" class="form-control">
    </div>
</div>