@php
    $imageUrl = $getImageUrl();
    $name = $getDivisionName();
    $icon = $getDivisionIcon();
    $frames = [
        'desktop' => ['label' => 'Desktop', 'width' => 1920, 'height' => 544, 'padding' => '160px 24px 80px', 'nameSize' => 72, 'row' => 'row'],
        'phone' => ['label' => 'Phone', 'width' => 390, 'height' => 600, 'padding' => '160px 16px 80px', 'nameSize' => 48, 'row' => 'column'],
    ];
    $veil = 'linear-gradient(to bottom, rgba(12, 12, 12, 0.9) 0, rgba(12, 12, 12, 0.55) 72px, transparent 168px), radial-gradient(ellipse 55% 70% at 85% 35%, rgba(188, 9, 19, 0.18), transparent 70%), linear-gradient(to right, rgba(12, 12, 12, 0.85) 0%, rgba(12, 12, 12, 0.45) 45%, rgba(12, 12, 12, 0.08) 100%), radial-gradient(ellipse 70% 60% at 50% 45%, transparent, rgba(12, 12, 12, 0.5)), linear-gradient(to bottom, rgba(27, 2, 2, 0.35), rgba(12, 12, 12, 0.1) 40%, #0c0c0c)';
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:key="hero-positioner-{{ md5($imageUrl ?? '') }}"
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            url: @js($imageUrl),
            natural: null,
            frames: @js(collect($frames)->map(fn ($frame) => ['w' => $frame['width'], 'h' => $frame['height']])),
            scales: { desktop: 0, phone: 0 },
            drag: null,
            init() {
                if (this.url) {
                    const image = new Image();
                    image.onload = () => this.natural = { w: image.naturalWidth, h: image.naturalHeight };
                    image.src = this.url;
                }
                const fit = () => Object.keys(this.frames).forEach((key) => {
                    const slot = this.$refs[key];
                    if (slot) this.scales[key] = slot.clientWidth / this.frames[key].w;
                });
                new ResizeObserver(fit).observe(this.$el);
                fit();
            },
            get point() {
                const match = /^(\d{1,3})% (\d{1,3})%$/.exec(this.state ?? '');
                return match ? { x: Number(match[1]), y: Number(match[2]) } : { x: 50, y: 50 };
            },
            get position() {
                return `${this.point.x}% ${this.point.y}%`;
            },
            place(x, y) {
                const clamp = (value) => Math.round(Math.min(100, Math.max(0, value)));
                this.state = `${clamp(x)}% ${clamp(y)}%`;
            },
            overflow(key) {
                if (!this.natural) return { x: 0, y: 0 };
                const box = { w: this.frames[key].w * 1.08, h: this.frames[key].h * 1.08 };
                const scale = Math.max(box.w / this.natural.w, box.h / this.natural.h);
                return { x: this.natural.w * scale - box.w, y: this.natural.h * scale - box.h };
            },
            start(event, key) {
                this.drag = { key, x: event.clientX, y: event.clientY, from: this.point };
                event.currentTarget.setPointerCapture(event.pointerId);
            },
            move(event) {
                if (!this.drag) return;
                const room = this.overflow(this.drag.key);
                const scale = this.scales[this.drag.key] || 1;
                const dx = (event.clientX - this.drag.x) / scale;
                const dy = (event.clientY - this.drag.y) / scale;
                this.place(
                    room.x > 0 ? this.drag.from.x - (dx / room.x) * 100 : this.drag.from.x,
                    room.y > 0 ? this.drag.from.y - (dy / room.y) * 100 : this.drag.from.y,
                );
            },
            nudge(dx, dy) {
                this.place(this.point.x + dx, this.point.y + dy);
            },
        }"
        style="display: grid; gap: 0.75rem;"
    >
        <div style="display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr) 9.5rem; align-items: start;">
            @foreach ($frames as $key => $frame)
                <figure style="margin: 0; min-width: 0;">
                    <div
                        x-ref="{{ $key }}"
                        tabindex="0"
                        role="slider"
                        aria-label="{{ $frame['label'] }} header preview. Drag or use the arrow keys to move the image."
                        :aria-valuenow="point.x"
                        :aria-valuetext="'Image position ' + position"
                        x-on:pointerdown="start($event, '{{ $key }}')"
                        x-on:pointermove="move($event)"
                        x-on:pointerup="drag = null"
                        x-on:pointercancel="drag = null"
                        x-on:keydown.arrow-left.prevent="nudge(2, 0)"
                        x-on:keydown.arrow-right.prevent="nudge(-2, 0)"
                        x-on:keydown.arrow-up.prevent="nudge(0, 2)"
                        x-on:keydown.arrow-down.prevent="nudge(0, -2)"
                        :style="{ cursor: drag ? 'grabbing' : 'grab' }"
                        style="position: relative; overflow: hidden; aspect-ratio: {{ $frame['width'] }} / {{ $frame['height'] }}; border-radius: 0.375rem; outline-offset: 2px; touch-action: none; user-select: none; box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.1);"
                    >
                        <div
                            :style="{ transform: `scale(${scales['{{ $key }}']})` }"
                            style="position: absolute; top: 0; left: 0; width: {{ $frame['width'] }}px; height: {{ $frame['height'] }}px; transform-origin: 0 0; overflow: hidden; background: #0c0c0c; color: #e5e5e5; pointer-events: none;"
                        >
                            <div
                                :style="{ backgroundPosition: position }"
                                style="position: absolute; inset: -4%; background-image: url('{{ $imageUrl }}'); background-size: cover; background-repeat: no-repeat; opacity: 0.85; filter: saturate(0.8) contrast(1.08);"
                            ></div>
                            <div style="position: absolute; inset: 0; background: {{ $veil }};"></div>
                            <div style="position: absolute; inset: 0; background: repeating-linear-gradient(to bottom, rgba(255, 255, 255, 0.025) 0 1px, transparent 1px 4px); mask-image: linear-gradient(to bottom, #000 60%, transparent);"></div>
                            <div style="position: relative; padding: {{ $frame['padding'] }};">
                                <div style="max-width: 1152px; margin: 0 auto;">
                                    <div style="font-family: ui-monospace, monospace; font-size: 12px; letter-spacing: 0.1em; text-transform: uppercase; color: #8a8a8a;">← All divisions</div>
                                    <div style="display: flex; flex-direction: {{ $frame['row'] }}; gap: 24px; margin-top: 24px; align-items: {{ $frame['row'] === 'row' ? 'center' : 'flex-start' }};">
                                        @if ($icon)
                                            <img src="{{ $icon }}" alt="" style="width: 80px; height: 80px; object-fit: contain;">
                                        @endif
                                        <div>
                                            <div style="font-family: ui-monospace, monospace; font-size: 12px; letter-spacing: 0.3em; text-transform: uppercase; color: #f04747;">AOD Division</div>
                                            <div style="margin-top: 4px; font-size: {{ $frame['nameSize'] }}px; font-weight: 700; line-height: 1; text-transform: uppercase; letter-spacing: 0.02em;">{{ $name }}</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px;">
                                        <span style="padding: 14px 28px; background: #bc0913; color: #fff; font-size: 14px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;">Join</span>
                                        <span style="padding: 12px 16px; background: rgba(0, 0, 0, 0.45); box-shadow: inset 0 0 0 1px #2f2e31; font-family: ui-monospace, monospace; font-size: 13px;">Members</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <figcaption style="margin-top: 0.375rem; font-size: 0.75rem; opacity: 0.7;">{{ $frame['label'] }}</figcaption>
                </figure>
            @endforeach
        </div>

        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; font-size: 0.875rem;">
            <span style="opacity: 0.75;">Drag either preview to choose what stays in frame. Both use the site's dimming and fade.</span>
            <span style="display: inline-flex; align-items: center; gap: 0.75rem;">
                <code x-text="position" style="font-size: 0.75rem; opacity: 0.75;"></code>
                <x-filament::link tag="button" type="button" size="sm" x-on:click="state = null">Reset to center</x-filament::link>
            </span>
        </div>
    </div>
</x-dynamic-component>
