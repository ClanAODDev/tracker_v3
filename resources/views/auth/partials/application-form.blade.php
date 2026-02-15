<div class="panel panel-filled animate-fade-in-up auth__panel">
    <div class="auth__pattern"></div>
    <div class="panel-body">
        <p class="auth__intro text-center m-b-lg">
            Almost there! Please complete this application for your selected division.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p class="m-b-none">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <fieldset {{ ($preview ?? false) ? 'disabled' : '' }}>
        <form action="{{ route('auth.discord.application') }}" method="POST">
            @csrf

            @foreach ($applicationFields as $field)
                <div class="form-group">
                    <label for="field_{{ $field->id }}">
                        {{ $field->label }}
                        @if ($field->required)
                            <span class="text-danger">*</span>
                        @endif
                    </label>

                    @if ($field->helper_text)
                        <p class="help-block text-muted m-b-sm">{{ $field->helper_text }}</p>
                    @endif

                    @if ($field->type === 'text')
                        <input
                            type="text"
                            name="field_{{ $field->id }}"
                            id="field_{{ $field->id }}"
                            class="form-control"
                            value="{{ old("field_{$field->id}") }}"
                            maxlength="500"
                            {{ $field->required ? 'required' : '' }}
                        >
                    @elseif ($field->type === 'textarea')
                        <textarea
                            name="field_{{ $field->id }}"
                            id="field_{{ $field->id }}"
                            class="form-control"
                            style="resize: vertical"
                            rows="4"
                            maxlength="500"
                            {{ $field->required ? 'required' : '' }}
                        >{{ old("field_{$field->id}") }}</textarea>
                    @elseif ($field->type === 'radio')
                        @foreach ($field->options as $option)
                            <div class="radio">
                                <label>
                                    <input
                                        type="radio"
                                        name="field_{{ $field->id }}"
                                        value="{{ $option['label'] }}"
                                        {{ old("field_{$field->id}") === $option['label'] ? 'checked' : '' }}
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                    {{ $option['label'] }}
                                </label>
                            </div>
                        @endforeach
                    @elseif ($field->type === 'checkbox')
                        @foreach ($field->options as $option)
                            <div class="checkbox">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="field_{{ $field->id }}[]"
                                        value="{{ $option['label'] }}"
                                        {{ is_array(old("field_{$field->id}")) && in_array($option['label'], old("field_{$field->id}")) ? 'checked' : '' }}
                                    >
                                    {{ $option['label'] }}
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach

            @if (! ($preview ?? false))
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        Submit Application <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            @endif
        </form>
        </fieldset>
    </div>
</div>
