@php
    $depth = count($levels);
    $labelWidth = 150;
    $cell = 52;
    $nodeW = 40;
    $nodeH = 26;
    $row = 72;
    $top = 20;
    $leaves = 2 ** $depth;
    $width = $labelWidth + $leaves * $cell;
    $height = $top + $depth * $row + $nodeH + 8;
    $xs = [$depth => array_map(fn ($i) => $labelWidth + ($i + 0.5) * $cell, range(0, $leaves - 1))];
    for ($d = $depth - 1; $d >= 0; $d--) {
        $xs[$d] = array_map(fn ($i) => ($xs[$d + 1][2 * $i] + $xs[$d + 1][2 * $i + 1]) / 2, range(0, 2 ** $d - 1));
    }
    $y = fn (int $d) => $top + $d * $row;
    $tierFill = ['platoon' => 'var(--primary-500)', 'squad' => 'var(--warning-500)'];
    $fillFor = function (int $d, int $i) use ($highlight, $levels, $tierFill) {
        if ($d === 0) return ['var(--gray-500)', 1, null];
        if ($highlight === null) return [$tierFill[$levels[$d - 1]['tier']], 1, null];
        if ($d === $highlight && $i === 0) return ['var(--primary-500)', 1, 'var(--primary-500)'];
        if ($d > $highlight && ($i >> ($d - $highlight)) === 0) return ['var(--primary-500)', 0.35, 'var(--primary-500)'];
        return ['currentColor', 0.08, 'currentColor'];
    };
@endphp
<svg viewBox="0 0 {{ $width }} {{ $height }}" style="width: 100%; max-width: {{ $width }}px; height: auto;" role="img">
    @foreach ($xs as $d => $rowXs)
        @if ($d > 0)
            @foreach ($rowXs as $i => $x)
                @php $px = $xs[$d - 1][$i >> 1]; $midY = $y($d) - ($row - $nodeH) / 2; @endphp
                <path d="M{{ $px }} {{ $y($d - 1) + $nodeH }} V{{ $midY }} H{{ $x }} V{{ $y($d) }}" fill="none" stroke="currentColor" stroke-opacity="0.2" />
            @endforeach
        @endif
    @endforeach
    @foreach ($xs as $d => $rowXs)
        @foreach ($rowXs as $i => $x)
            @php
                [$fill, $opacity, $stroke] = $fillFor($d, $i);
                $w = $d === 0 ? $nodeW * 2 : $nodeW;
            @endphp
            <rect x="{{ $x - $w / 2 }}" y="{{ $y($d) }}" width="{{ $w }}" height="{{ $nodeH }}" rx="4" fill="{{ $fill }}" fill-opacity="{{ $opacity }}" @if ($stroke) stroke="{{ $stroke }}" stroke-opacity="{{ $opacity === 0.08 ? 0.25 : 1 }}" @endif />
        @endforeach
    @endforeach
    <text x="6" y="{{ $y(0) + $nodeH - 7 }}" fill="currentColor" fill-opacity="0.6" font-size="14">Division</text>
    @foreach ($levels as $index => $level)
        <text x="6" y="{{ $y($index + 1) + $nodeH - 7 }}" fill="currentColor" fill-opacity="{{ $highlight === $index + 1 ? 1 : 0.6 }}" font-size="14" font-weight="{{ $highlight === $index + 1 ? 600 : 400 }}">{{ $level['label'] ?: 'Level ' . ($index + 1) }}</text>
    @endforeach
</svg>
