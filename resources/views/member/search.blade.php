@foreach($members as $member)
    <a class="panel" href="{{ route('member', $member->clan_id) }}">
        <div class="panel-body">
            <h4 class="m-b-none">
                {!! $member->present()->rankName !!}
            </h4>
        </div>
    </a>
@endforeach

<table class="table table-stripe table-hover">
    @foreach ($members as $member)
        <tr><td>{!! $member->present()->rankName !!}</td></tr>
    @endforeach
</table>
