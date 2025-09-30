@foreach ($promotions->groupBy(fn ($p) => $p->rank?->name ?? 'Unspecified') as $rank => $actions)
    [b]{{ strtoupper($rank) }}[/b]
    [list]
    @foreach ($actions as $action)
        [*]{{ $action->member?->name }}
    @endforeach
[/list]

@endforeach
