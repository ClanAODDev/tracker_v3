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
                    <a href="#" class="panel panel-filled">
                        <div class="panel-body" style="overflow: hidden" title="{{ $record->award->description }}">
                            <img src="{{ asset(Storage::url($record->award->image)) }}"
                                 alt="{{ $record->award->name }}"
                                 class="align-self-start">
                            <p style="text-align: right;" class="pull-right text-nowrap">
                                <span class="c-white">{{ $record->award->name }}</span> <br/>
                                Earned {{ $record->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif