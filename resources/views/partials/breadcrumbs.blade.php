@unless ($breadcrumbs->isEmpty())
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb->title === 'Reports' && !$loop->last)
                    <li class="breadcrumb-item">
                        <div class="breadcrumb-dropdown dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                Reports <i class="fa fa-caret-down"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-dark">
                                @php
                                    $division = request()->route('division');
                                @endphp
                                <a class="dropdown-item" href="{{ route('division.census', $division) }}">
                                    <i class="fa fa-chart-line"></i> Census
                                </a>
                                <a class="dropdown-item" href="{{ route('division.retention-report', $division) }}">
                                    <i class="fa fa-chart-area"></i> Retention
                                </a>
                                <a class="dropdown-item" href="{{ route('division.promotions', $division) }}">
                                    <i class="fa fa-medal"></i> Promotions
                                </a>
                                <a class="dropdown-item" href="{{ route('division.voice-report', $division) }}">
                                    <i class="fa fa-headset"></i> Voice
                                </a>
                            </div>
                        </div>
                    </li>
                @elseif ($breadcrumb->url && !$loop->last)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
                @else
                    <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endunless
