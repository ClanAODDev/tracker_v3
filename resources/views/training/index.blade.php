@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Leadership Training
        @endslot
        @slot ('icon')
            <img src="{{ getThemedLogoPath() }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Training Modules
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            @foreach($modules as $module)
                <div class="col-md-4 col-sm-6">
                    <a href="{{ route('training.show', $module->slug) }}" class="panel panel-filled panel-clickable">
                        <div class="panel-body">
                            <h4 class="m-t-none m-b-xs">{{ $module->name }}</h4>
                            @if($module->description)
                                <p class="text-muted m-b-none">{{ $module->description }}</p>
                            @endif
                        </div>
                        <div class="panel-footer text-right">
                            <small class="text-muted">{{ $module->sections_count }} sections</small>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

@endsection
