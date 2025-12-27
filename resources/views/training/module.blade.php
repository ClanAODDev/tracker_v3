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
            {{ $module->name }}
        @endslot
    @endcomponent

    <div class="container-fluid" id="training-container">

        @include('application.partials.errors')

        <div class="training-stepper">
            <div class="training-stepper__sidebar">
                <div class="training-stepper__header">
                    <div class="training-stepper__header-top">
                        <h4><i class="fa fa-graduation-cap"></i> AOD Leader Training</h4>
                        <button type="button" class="training-stepper__fullscreen-toggle" title="Toggle fullscreen">
                            <i class="fa fa-expand"></i>
                        </button>
                    </div>
                    @if($trainee)
                        <div class="trainee-info">
                            Trainee: <strong>{!! $trainee->present()->rankName !!}</strong>
                        </div>
                    @endif
                </div>

                <div class="training-stepper__steps">
                    @foreach($module->sections as $index => $section)
                        <div class="training-stepper__step {{ $index === 0 ? 'active' : '' }}"
                             data-step="{{ $index }}">
                            <div class="training-stepper__step-indicator">
                                <i class="{{ str_contains($section->icon ?? '', 'fab') ? '' : 'fa' }} {{ $section->icon ?? 'fa-circle' }}"></i>
                            </div>
                            <div class="training-stepper__step-label">
                                {{ $section->title }}
                            </div>
                            <div class="training-stepper__step-status">
                                <span class="checkpoint-count">0</span>/{{ $section->checkpoints->count() }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="training-stepper__progress">
                    <div class="training-stepper__progress-bar">
                        <div class="training-stepper__progress-fill" style="width: 0%"></div>
                    </div>
                    <div class="training-stepper__progress-text">
                        <span class="progress-label">Progress</span>
                        <span class="progress-count">
                            <span class="total-complete">0</span> / <span class="total-checkpoints">{{ $module->sections->sum(fn($s) => $s->checkpoints->count()) }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="training-stepper__content">
                @foreach($module->sections as $index => $section)
                    <div class="training-stepper__section {{ $index === 0 ? 'active' : '' }}"
                         data-section="{{ $index }}">
                        <div class="training-stepper__section-header">
                            <h3><i class="{{ str_contains($section->icon ?? '', 'fab') ? '' : 'fa' }} {{ $section->icon ?? 'fa-circle' }}"></i> {{ $section->title }}</h3>
                        </div>

                        <div class="training-stepper__section-body">
                            <div class="training-stepper__markdown">
                                @markdown($section->content)
                            </div>

                            @if($section->checkpoints->count() > 0)
                                <div class="training-stepper__checkpoints">
                                    <h5><i class="fa fa-clipboard-check"></i> {{ $module->checkpoint_label }}</h5>
                                    <div class="training-stepper__checkpoint-list">
                                        @foreach($section->checkpoints as $checkpoint)
                                            <div class="training-stepper__checkpoint-wrapper">
                                                <label class="training-stepper__checkpoint"
                                                       data-section="{{ $index }}">
                                                    <input type="checkbox"
                                                           class="training-checkpoint"
                                                           data-section="{{ $index }}">
                                                    <span class="training-stepper__checkbox"></span>
                                                    <span class="training-stepper__checkpoint-label">{{ $checkpoint->label }}</span>
                                                    @if($checkpoint->description)
                                                        <button type="button" class="training-stepper__checkpoint-toggle">
                                                            <i class="fa fa-chevron-down"></i>
                                                        </button>
                                                    @endif
                                                </label>
                                                @if($checkpoint->description)
                                                    <div class="training-stepper__checkpoint-description">
                                                        <div class="training-stepper__markdown">
                                                            @markdown($checkpoint->description)
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="training-stepper__section-footer">
                            <div class="training-stepper__nav">
                                @if($index > 0)
                                    <button type="button"
                                            class="btn btn-default training-stepper__prev"
                                            data-target="{{ $index - 1 }}">
                                        <i class="fa fa-arrow-left"></i> Previous
                                    </button>
                                @else
                                    <span></span>
                                @endif

                                @if($index < $module->sections->count() - 1)
                                    <button type="button"
                                            class="btn btn-primary training-stepper__next"
                                            data-target="{{ $index + 1 }}">
                                        Next <i class="fa fa-arrow-right"></i>
                                    </button>
                                @else
                                    <span></span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if(request()->has('training') && $module->show_completion_form)
            <div class="training-stepper__completion">
                <div class="panel panel-filled panel-c-success">
                    <div class="panel-heading">
                        <i class="fa fa-check-circle"></i> Complete Training
                    </div>
                    <div class="panel-body">
                        <p>Once you are finished with the training session, search for the member
                            you are training and submit.</p>
                        <p class="text-muted m-b-none">This will update the member's last training date and set you as the trainer.</p>
                    </div>
                    <div class="panel-footer">
                        <form action="{{ route('training.update') }}" method="POST" class="training-stepper__form">
                            @csrf
                            <input type="hidden" name="module" value="{{ $module->slug }}">
                            <div class="training-stepper__form-row">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text"
                                           class="form-control search-member"
                                           id="trainee_name"
                                           name="trainee_name"
                                           autocomplete="off"
                                           placeholder="Search for member..."
                                           value="{{ $trainee?->name }}"
                                    />
                                </div>
                                <input type="hidden" name="clan_id" id="clan_id" value="{{ $trainee?->clan_id }}">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check"></i> Complete Training
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/training.js'])
@endsection
