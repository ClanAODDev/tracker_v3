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
        <div class="row">
            @foreach ($member->awards->sortBy('display_order') as $index => $record)
                <div class="col-lg-3">
                    <a href="#" class="btn btn-default btn-block" style="margin-bottom:20px;">
                        <div class="panel-body" title="{{ $record->award->description }}">
                            <img src="{{ asset(Storage::url($record->award->image)) }}"
                                 alt="{{ $record->award->name }}"
                                 style="overflow: hidden"
                                 class="align-self-start pull-left">
                            <small style="text-align: right; padding-top:10px; " class="pull-right">
                                <strong><span class="c-white">{{ $record->award->name }}</span></strong> <br/>
                                <span class="text-muted">{{ $record->created_at->format('M d, Y') }}</span>
                            </small>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif