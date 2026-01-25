<div class="settings-overlay"></div>
<aside class="settings-slideover">
    <div class="settings-header">
        <span class="settings-title">Settings</span>
        <button class="settings-close"><i class="fa fa-times"></i></button>
    </div>
    <div class="settings-content">
        <form id="user-settings-form">
            @csrf
            <div class="settings-section">
                <h4 class="settings-section-title">Appearance</h4>

                <div class="settings-field">
                    <label class="settings-toggle-label">
                        <span>Disable animations</span>
                        <div class="settings-switch">
                            <input type="checkbox" name="disable_animations" id="setting-disable-animations"
                                {{ (auth()->user()->settings['disable_animations'] ?? false) ? 'checked' : '' }}>
                            <span class="settings-switch-slider"></span>
                        </div>
                    </label>
                    <p class="settings-help">Disable page animations for a simpler experience</p>
                </div>

                <div class="settings-field">
                    <label class="settings-label">Mobile navigation position</label>
                    <div class="settings-button-group">
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['mobile_nav_side'] ?? 'right') === 'left' ? 'active' : '' }}" data-value="left" data-setting="mobile_nav_side">
                            <i class="fa fa-arrow-left"></i> Left
                        </button>
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['mobile_nav_side'] ?? 'right') === 'right' ? 'active' : '' }}" data-value="right" data-setting="mobile_nav_side">
                            Right <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                    <input type="hidden" name="mobile_nav_side" id="setting-mobile-nav-side" value="{{ auth()->user()->settings['mobile_nav_side'] ?? 'right' }}">
                </div>

                <div class="settings-field">
                    <label class="settings-label">Particle effects</label>
                    <div class="settings-button-group settings-button-group-4">
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['snow'] ?? 'no_snow') === 'no_snow' ? 'active' : '' }}" data-value="no_snow" data-setting="snow">
                            None
                        </button>
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['snow'] ?? 'no_snow') === 'some_snow' ? 'active' : '' }}" data-value="some_snow" data-setting="snow">
                            Snow
                        </button>
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['snow'] ?? 'no_snow') === 'all_the_snow' ? 'active' : '' }}" data-value="all_the_snow" data-setting="snow">
                            Blizzard
                        </button>
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['snow'] ?? 'no_snow') === 'motes' ? 'active' : '' }}" data-value="motes" data-setting="snow">
                            <i class="fa fa-star-o"></i> Motes
                        </button>
                    </div>
                    <input type="hidden" name="snow" id="setting-snow" value="{{ auth()->user()->settings['snow'] ?? 'no_snow' }}">
                </div>

                <div class="settings-field" id="snow-mouse-setting" style="{{ (auth()->user()->settings['snow'] ?? 'no_snow') === 'no_snow' ? 'display: none;' : '' }}">
                    <label class="settings-toggle-label">
                        <span>Particles ignore mouse</span>
                        <label class="settings-switch">
                            <input type="checkbox" name="snow_ignore_mouse" id="setting-snow-ignore-mouse"
                                   {{ (auth()->user()->settings['snow_ignore_mouse'] ?? false) ? 'checked' : '' }}>
                            <span class="settings-switch-slider"></span>
                        </label>
                    </label>
                    <p class="settings-help">Prevents particles from following mouse cursor</p>
                </div>

                <div class="settings-field">
                    <label class="settings-label">Theme</label>
                    <div class="settings-button-group">
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['theme'] ?? 'traditional') === 'traditional' ? 'active' : '' }}" data-value="traditional" data-setting="theme">
                            <span class="theme-swatch theme-traditional"></span> Traditional
                        </button>
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['theme'] ?? 'traditional') === 'shattrath' ? 'active' : '' }}" data-value="shattrath" data-setting="theme">
                            <span class="theme-swatch theme-shattrath"></span> Shattrath
                        </button>
                        {{-- Light theme hidden until ready
                        <button type="button" class="settings-btn {{ (auth()->user()->settings['theme'] ?? 'traditional') === 'light' ? 'active' : '' }}" data-value="light" data-setting="theme">
                            <span class="theme-swatch theme-light"></span> Light
                        </button>
                        --}}
                    </div>
                    <input type="hidden" name="theme" id="setting-theme" value="{{ auth()->user()->settings['theme'] ?? 'traditional' }}">
                </div>
            </div>

            @if(auth()->user()->member)
                <div class="settings-section">
                    <h4 class="settings-section-title">Profile</h4>

                    <div class="settings-field">
                        <button type="button" class="settings-link-btn" data-toggle="modal" data-target="#part-time-divisions-modal">
                            <span>
                                <i class="fa fa-puzzle-piece"></i>
                                Part-Time Divisions
                            </span>
                            <span class="settings-link-count">{{ auth()->user()->member->partTimeDivisions()->count() }}</span>
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="settings-field">
                        <button type="button" class="settings-link-btn" data-toggle="modal" data-target="#ingame-handles-modal">
                            <span>
                                <i class="fa fa-gamepad"></i>
                                In-Game Handles
                            </span>
                            <span class="settings-link-count">{{ auth()->user()->member->memberHandles()->count() }}</span>
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                @php
                    $settingsMember = auth()->user()->member;
                    $pendingTransfer = $settingsMember->transfers()->pending()->first();
                    $canRequestTransfer = $settingsMember->division_id
                        && $settingsMember->division?->name !== 'Floater';
                @endphp

                @if($settingsMember->division && $canRequestTransfer)
                    <div class="settings-section">
                        <h4 class="settings-section-title">Primary Division</h4>

                        <div class="settings-field">
                            <div class="settings-division-info">
                                <div class="settings-division-display">
                                    <img src="{{ $settingsMember->division->getLogoPath() }}" alt="" class="settings-division-logo">
                                    <span class="settings-division-name">{{ $settingsMember->division->name }}</span>
                                </div>
                            </div>
                        </div>

                        @if($pendingTransfer)
                            <div class="settings-field">
                                <div class="settings-transfer-pending">
                                    <i class="fa fa-clock-o"></i>
                                    <div>
                                        <div>Transfer to {{ $pendingTransfer->division->name }} pending</div>
                                        <small class="text-muted">Awaiting {{ $pendingTransfer->division->name }} leadership approval</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="settings-field">
                                <button type="button" class="settings-link-btn" data-toggle="modal" data-target="#transfer-request-modal">
                                    <span><i class="fa fa-exchange"></i> Request Division Transfer</span>
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </form>
    </div>
    <div class="settings-footer">
        <span class="settings-save-status"></span>
    </div>
</aside>

@if(auth()->user()->member)
    @php
        $member = auth()->user()->member;
        $excludedDivisions = \App\Models\Division::whereIn('name', ['Floater', "Bluntz' Reserves"])->pluck('id')->toArray();
        $availableDivisions = \App\Models\Division::active()
            ->whereNotIn('id', $excludedDivisions)
            ->when($member->division_id, fn($q) => $q->where('id', '!=', $member->division_id))
            ->orderBy('name')
            ->get();
        $selectedPartTime = $member->partTimeDivisions()->pluck('divisions.id')->toArray();
        $handles = \App\Models\Handle::orderBy('label')->get();
        $memberHandles = $member->memberHandles()->with('handle')->get();
        $transferableDivisions = \App\Models\Division::active()
            ->whereNull('shutdown_at')
            ->whereNotIn('id', $excludedDivisions)
            ->where('id', '!=', $member->division_id)
            ->orderBy('name')
            ->get();
    @endphp

    <div class="modal fade" id="part-time-divisions-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-puzzle-piece text-accent"></i> Part-Time Divisions</h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Select divisions you participate in part-time (in addition to your primary division).</p>
                    <form id="part-time-divisions-form">
                        @csrf
                        <div class="part-time-divisions-grid">
                            @foreach($availableDivisions as $division)
                                <label class="part-time-division-item">
                                    <input type="checkbox" name="divisions[]" value="{{ $division->id }}"
                                        {{ in_array($division->id, $selectedPartTime) ? 'checked' : '' }}>
                                    <span class="part-time-division-name">{{ $division->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <span class="modal-save-status"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-accent" id="save-part-time-divisions">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ingame-handles-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-gamepad text-accent"></i> In-Game Handles</h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Add your usernames for different games and platforms so other members can find and play with you.</p>
                    <form id="ingame-handles-form">
                        @csrf
                        <div id="handles-container">
                            @foreach($memberHandles as $index => $mh)
                                <div class="handle-row" data-id="{{ $mh->id }}">
                                    <select name="handles[{{ $index }}][handle_id]" class="form-control handle-select" required>
                                        <option value="">Select game/platform...</option>
                                        @foreach($handles as $handle)
                                            <option value="{{ $handle->id }}" {{ $mh->handle_id == $handle->id ? 'selected' : '' }}>
                                                {{ $handle->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="handles[{{ $index }}][value]" class="form-control"
                                           placeholder="Your username..." value="{{ $mh->value }}" required>
                                    <label class="handle-primary">
                                        <input type="checkbox" name="handles[{{ $index }}][primary]" {{ $mh->primary ? 'checked' : '' }}>
                                        Primary
                                    </label>
                                    <input type="hidden" name="handles[{{ $index }}][id]" value="{{ $mh->id }}">
                                    <button type="button" class="btn btn-sm btn-danger remove-handle"><i class="fa fa-times"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-default" id="add-handle">
                            <i class="fa fa-plus"></i> Add Handle
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <span class="modal-save-status"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-accent" id="save-ingame-handles">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transfer-request-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-exchange text-accent"></i> Request Division Transfer</h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Select the division you would like to transfer to.
                        @if($member->rank->isOfficer())
                            As an officer, your request will require approval from the gaining division's leadership.
                        @else
                            Your transfer will be processed automatically.
                        @endif
                    </p>
                    <form id="transfer-request-form">
                        @csrf
                        <div class="form-group">
                            <label for="transfer-division-select">Transfer to Division</label>
                            <select id="transfer-division-select" name="division_id" class="form-control" required>
                                <option value="">Select a division...</option>
                                @foreach($transferableDivisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-accent" id="save-transfer-request">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <template id="handle-row-template">
        <div class="handle-row" data-id="">
            <select name="handles[__INDEX__][handle_id]" class="form-control handle-select" required>
                <option value="">Select game/platform...</option>
                @foreach($handles as $handle)
                    <option value="{{ $handle->id }}">{{ $handle->label }}</option>
                @endforeach
            </select>
            <input type="text" name="handles[__INDEX__][value]" class="form-control" placeholder="Your username..." required>
            <label class="handle-primary">
                <input type="checkbox" name="handles[__INDEX__][primary]">
                Primary
            </label>
            <input type="hidden" name="handles[__INDEX__][id]" value="">
            <button type="button" class="btn btn-sm btn-danger remove-handle"><i class="fa fa-times"></i></button>
        </div>
    </template>
@endif
