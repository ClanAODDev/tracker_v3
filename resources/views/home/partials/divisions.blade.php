@foreach($divisions as $division)
    {{-- style="animation-delay: {{ $loop->index/8 }}s;" --}}
    <div class="col-lg-4 col-md-6">
        <a href="{{ route('division', $division->slug) }}"
           class="panel panel-filled division-header {{ ($division->isShutdown()) ? 'panel-c-danger' : null }}">
            <div class="panel-body">
                <h4 class="m-b-none text-uppercase m-t-sm">
                    <img src="{{ $division->getLogoPath() }}"
                         class="pull-right division-icon-medium" alt=""/>
                    @if ($division->isShutDown())
                        <span title="Division is shut down"
                              style="text-decoration: line-through;">{{ $division->name }}</span>
                    @else
                        {{ $division->name }}
                    @endif

                </h4>
                <span class="small">{{ $division->members_count }} MEMBERS</span>
            </div>
        </a>
    </div>
@endforeach