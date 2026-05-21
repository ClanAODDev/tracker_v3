<div class="err-stage">
    <div class="err-content">
        <div class="err-code" data-text="{{ $code }}">{{ $code }}</div>
        <div class="err-divider"></div>
        <p class="err-title">{{ $title }}</p>
        <p class="err-message">{!! $message !!}</p>

        @if (config('app.debug') && !empty($exception) && !empty($exception->getMessage()))
            <div class="err-exception">{{ $exception->getMessage() }}</div>
        @endif

        @if ($showHome ?? true)
            <a href="{{ route('home') }}" class="btn btn-accent">Return to Base</a>
        @endif

        <div class="err-pong-toggle">
            <button id="pong-btn">▶ play pong</button>
        </div>

        <div id="err-pong-wrapper" class="err-pong-wrapper">
            <canvas id="pong-canvas" width="500" height="220"></canvas>
            <p class="err-pong-hint">move mouse to control left paddle</p>
        </div>
    </div>
</div>

@vite('resources/assets/js/error-pong.js')
