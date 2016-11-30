<div class="row margin-top-50">
    @foreach($squads as $squad)
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Squad #{{ $loop->index + 1 }} <span class="badge pull-right">{{ $squad->members->count() }}</span>
                </div>

                @if($squad->members->count())
                    <div style="max-height: 200px; overflow-y: scroll;">
                        @foreach($squad->members as $member)
                            <a href="{{ action('MemberController@show', $member->clan_id) }}"
                               class="list-group-item">
                                <span class="col-xs-6">
                                    {!! $member->present()->nameWithIcon !!}
                                </span>
                                <span class="col-xs-6 text-center">
                                    <span class="{{ $member->activity['class'] }}">{{ $member->present()->lastActive }}</span>
                                </span>
                                <span class="clearfix"></span>
                            </a>
                        @endforeach
                    </div>
                    <div class="panel-footer text-muted text-center">
                        <small>Scroll for more <i class="fa fa-caret-down"></i></small>
                    </div>
                @else
                    <div class="panel-body text-muted">No members assigned</div>
                @endif

            </div>
        </div>
    @endforeach
</div>