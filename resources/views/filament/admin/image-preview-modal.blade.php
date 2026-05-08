<div
    x-data="{
        images: [],
        index: 0,
        open: false,
        get src() { return this.images[this.index] || ''; },
        get count() { return this.images.length; },
        prev() { this.index = (this.index - 1 + this.count) % this.count; },
        next() { this.index = (this.index + 1) % this.count; },
        openAt(src) {
            this.images = Array.from(document.querySelectorAll('[data-preview]')).map(function(el){ return el.src; });
            var idx = this.images.indexOf(src);
            this.index = idx >= 0 ? idx : 0;
            this.open = true;
        }
    }"
    x-on:open-image-preview.window="openAt($event.detail.src)"
    x-on:keydown.escape.window="open = false"
    x-on:keydown.arrow-left.window="open && count > 1 && prev()"
    x-on:keydown.arrow-right.window="open && count > 1 && next()"
    x-show="open"
    x-cloak
    style="position:fixed;inset:0;z-index:99999;"
>
    <div
        x-on:click.self="open = false"
        style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.88);"
    >
        <button
            type="button"
            x-on:click="open = false"
            style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:28px;line-height:1;cursor:pointer;opacity:.7;"
            x-on:mouseover="$el.style.opacity=1"
            x-on:mouseout="$el.style.opacity=.7"
        >&times;</button>

        <button
            type="button"
            x-show="count > 1"
            x-on:click="prev()"
            style="position:absolute;left:20px;background:rgba(255,255,255,0.1);border:none;color:#fff;font-size:28px;width:48px;height:48px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;"
            x-on:mouseover="$el.style.background='rgba(255,255,255,0.2)'"
            x-on:mouseout="$el.style.background='rgba(255,255,255,0.1)'"
        >&#8249;</button>

        <img
            :src="src"
            style="max-width:90vw;max-height:90vh;border-radius:4px;box-shadow:0 8px 32px rgba(0,0,0,.6);display:block;margin:auto;"
            alt="Screenshot preview"
        >

        <button
            type="button"
            x-show="count > 1"
            x-on:click="next()"
            style="position:absolute;right:20px;background:rgba(255,255,255,0.1);border:none;color:#fff;font-size:28px;width:48px;height:48px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;"
            x-on:mouseover="$el.style.background='rgba(255,255,255,0.2)'"
            x-on:mouseout="$el.style.background='rgba(255,255,255,0.1)'"
        >&#8250;</button>

        <div
            x-show="count > 1"
            x-text="(index + 1) + ' / ' + count"
            style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.6);font-size:14px;"
        ></div>
    </div>
</div>
