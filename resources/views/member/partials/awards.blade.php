@if ($member->awards->count())
    <h4 class="m-t-xl">
        Awards

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
                    <div class="col-lg-4">
                        <div class="panel panel-filled">
                            <div class="panel-body" style="overflow: hidden"  title="{{ $record->award->description }}">
                                <img src="{{ asset(Storage::url($record->award->image)) }}" alt="Award"
                                     class="align-self-start">
                                <p style="text-align: right;" class="pull-right text-nowrap">
                                    {{ $record->award->name }} <br/>
                                    <span class="text-muted">Earned {{ $record->created_at->format('M d, Y') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
            @endforeach
        </div>

        {{--        @foreach ($member->awards as $record)--}}
        {{--            <div class="col-lg-4">--}}
        {{--                <div class="panel panel-filled ">--}}
        {{--                <div class="panel-body" style="overflow: hidden">--}}
        {{--                        <img src="{{ asset(Storage::url($record->award->image)) }}" alt="Award" class="align-self-start">--}}
        {{--                        <p style="text-align: right;" class="pull-right  text-nowrap">{{--}}
        {{--                        $record->award->name--}}
        {{--                         }} <br />--}}
        {{--                            <span class="text-muted">Earned {{ $record->created_at->format('M d, Y') }}</span>--}}
        {{--                        </p>--}}
        {{--                </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        @endforeach--}}
    </div>
@endif