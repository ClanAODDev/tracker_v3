@if ($member->awards->count())
    <h4 class="m-t-xl">
        Achievements

        {{--    @can ('manageIngameHandles', $member)--}}
        {{--        <a href="{{ route('member.edit-handles', $member->clan_id) }}" class="btn btn-default pull-right">--}}
        {{--            <i class="fa fa-cog text-accent"></i> Manage--}}
        {{--        </a>--}}
        {{--    @endcan--}}
    </h4>
    <hr/>
    <div class="row">

        @foreach ($member->awards->sortBy('display_order') as $index => $record)
            <div class="col-lg-3 col-xl-2 col-sm-6">
                <a href="{{ route('awards.show', $record->award) }}" class="btn btn-default btn-block"
                   style="margin-bottom:20px;">
                    <div class="panel-body award-item" title="{{ $record->award->description }}">
                        <img src="{{ asset(Storage::url($record->award->image)) }}"
                             alt="{{ $record->award->name }}"
                             class="clan-award"
                        />
                        <div class="col-xs-9 p-0"
                             style="height:100%;display:flex;align-items: center;justify-content: center;">
                            <div class="text-align: right;">
                                <span class="c-white">{{ $record->award->name }}</span> <br/>
                                <span class="text-muted">{{ $record->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

@endif