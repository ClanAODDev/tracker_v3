<h4 class="m-t-xl">
    Handles

    @can ('manageIngameHandles', $member)
        @if ($member->id === auth()->user()->member_id)
            <a href="{{ route('filament.profile.pages.ingame-handles') }}"
               class="btn btn-default pull-right"><i class="fa fa-cog text-accent"></i> Manage
            </a>
        @else
            <a href="{{ route('filament.mod.resources.members.edit', $member) }}#ingame-handles" class="btn btn-default
        pull-right"><i class="fa fa-cog text-accent"></i> Manage
            </a>
        @endif
    @endcan
</h4>
<hr/>
<div class="row">
    @if($discordUrl = $member->getDiscordUrl())
        <div class="col-md-3" style="overflow: hidden;">
            <div class="panel panel-filled">
                <div class="panel-body">
                    <small class="c-white slight text-uppercase">
                        Discord Tag
                        <button data-clipboard-text="{{ $member->discord }}"
                                class="copy-to-clipboard btn-outline-warning btm-xs btn"
                                style="float: right;display: inline;">
                            <i class="far fa-copy" title="Copy to clipboard"></i>
                        </button>
                    </small>
                    <br/>
                    <span class="text-uppercase">{{$member->discord }}</span>
                </div>
            </div>
        </div>
    @endif
    @forelse ($member->handles as $handle)
        <div class="col-md-3" style="overflow: hidden;">

            @if($handle->url)
                @include('member.partials.handle-link')
            @else
                @include('member.partials.handle')
            @endif
        </div>

    @empty
        <div class="col-xs-12">
            <div class="panel panel-filled panel-c-warning">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No ingame handles
                    </h4>
                    <span class="slight">See division NCO for assistance with ingame handles</span>
                </div>
            </div>
        </div>
    @endforelse
</div>