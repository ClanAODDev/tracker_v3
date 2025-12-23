@if($member->discord || $member->handles->count())
    <h4 class="m-t-xl">
        Handles
        @can('manageIngameHandles', $member)
            @if($member->id === auth()->user()->member_id)
                <a href="{{ route('filament.profile.pages.ingame-handles') }}"
                   class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
            @else
                <a href="{{ route('filament.mod.resources.members.edit', $member) }}#ingame-handles"
                   class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
            @endif
        @endcan
    </h4>
    <hr/>

    @php
        $groupedHandles = $member->handles->groupBy('label');
    @endphp

    <div class="handles-grid">
        @if($member->discord)
            <div class="handle-card">
                <div class="handle-icon handle-discord">
                    <i class="fab fa-discord"></i>
                </div>
                <div class="handle-info">
                    <span class="handle-label">Discord</span>
                    <span class="handle-value">{{ $member->discord }}</span>
                </div>
                <div class="handle-actions">
                    <button data-clipboard-text="{{ $member->discord }}"
                            class="copy-to-clipboard btn btn-xs btn-default" title="Copy">
                        <i class="far fa-copy"></i>
                    </button>
                    @if($discordUrl = $member->getDiscordUrl())
                        <a href="{{ $discordUrl }}" target="_blank" class="btn btn-xs btn-default" title="Open Profile">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @foreach($groupedHandles as $label => $handles)
            @php
                $primary = $handles->first();
                $extras = $handles->slice(1);
                $modalId = 'handle-modal-' . Str::slug($label);
            @endphp
            <div class="handle-card">
                <div class="handle-icon">
                    <i class="fa fa-gamepad"></i>
                </div>
                <div class="handle-info">
                    <span class="handle-label">{{ $label }}</span>
                    <span class="handle-value">{{ $primary->pivot->value }}</span>
                </div>
                <div class="handle-actions">
                    <button data-clipboard-text="{{ $primary->pivot->value }}"
                            class="copy-to-clipboard btn btn-xs btn-default" title="Copy">
                        <i class="far fa-copy"></i>
                    </button>
                    @if($primary->url)
                        <a href="{{ $primary->full_url }}" target="_blank" class="btn btn-xs btn-default" title="Open Profile">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                    @if($extras->count() > 0)
                        <button class="btn btn-xs btn-default handle-more-btn"
                                data-toggle="modal" data-target="#{{ $modalId }}"
                                title="{{ $extras->count() }} more">
                            +{{ $extras->count() }}
                        </button>
                    @endif
                </div>
            </div>

            @if($extras->count() > 0)
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                <h4 class="modal-title">{{ $label }} Handles</h4>
                            </div>
                            <div class="modal-body">
                                <ul class="handle-list">
                                    @foreach($handles as $handle)
                                        <li class="handle-list-item">
                                            <span class="handle-list-value">{{ $handle->pivot->value }}</span>
                                            <div class="handle-list-actions">
                                                <button data-clipboard-text="{{ $handle->pivot->value }}"
                                                        class="copy-to-clipboard btn btn-xs btn-default" title="Copy">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                                @if($handle->url)
                                                    <a href="{{ $handle->full_url }}" target="_blank" class="btn btn-xs btn-default" title="Open Profile">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
