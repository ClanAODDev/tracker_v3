@foreach ($members->groupBy('rank.name') as $rank=>$members)[b]{{ $rank }}[/b][list]@foreach ($members as $member)[*]{{ $member->name }}@endforeach[/list]
@endforeach