@auth
<style>
.motes-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
    z-index: 0;
}

.mote {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    will-change: transform, opacity;
}
</style>

<script>
var motesEngine = null;

function initMotesOfLight(count, ignoreMouse) {
    if (motesEngine) {
        motesEngine.stop();
        motesEngine = null;
    }

    if (!count || count <= 0) {
        return;
    }

    motesEngine = new MotesEngine(count, ignoreMouse);
    motesEngine.start();
}

function MotesEngine(count, ignoreMouse) {
    this.count = count;
    this.ignoreMouse = ignoreMouse;
    this.motes = [];
    this.container = null;
    this.animationId = null;
    this.mouseX = window.innerWidth / 2;
    this.mouseY = window.innerHeight / 2;
    this.running = false;

    this.colors = [
        { bg: 'radial-gradient(circle, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.8) 40%, transparent 70%)',
          shadow: '0 0 6px 2px rgba(255, 255, 255, 0.6), 0 0 12px 4px rgba(255, 255, 255, 0.3)' },
        { bg: 'radial-gradient(circle, rgba(240, 240, 255, 1) 0%, rgba(230, 230, 250, 0.8) 40%, transparent 70%)',
          shadow: '0 0 6px 2px rgba(240, 240, 255, 0.5), 0 0 12px 4px rgba(230, 230, 250, 0.25)' },
        { bg: 'radial-gradient(circle, rgba(255, 255, 255, 1) 0%, rgba(245, 245, 255, 0.8) 40%, transparent 70%)',
          shadow: '0 0 8px 3px rgba(255, 255, 255, 0.5), 0 0 16px 6px rgba(255, 255, 255, 0.2)' }
    ];
}

MotesEngine.prototype.start = function() {
    var self = this;

    this.container = document.createElement('div');
    this.container.className = 'motes-container';
    this.container.id = 'motes-of-light';
    document.body.appendChild(this.container);

    for (var i = 0; i < this.count; i++) {
        this.createMote(i);
    }

    if (!this.ignoreMouse) {
        this.mouseMoveHandler = function(e) {
            self.mouseX = e.clientX;
            self.mouseY = e.clientY;
        };
        document.addEventListener('mousemove', this.mouseMoveHandler);
    }

    this.running = true;
    this.animate();
};

MotesEngine.prototype.createMote = function(index) {
    var el = document.createElement('div');
    el.className = 'mote';

    var colorIndex = index % 3;
    var size = colorIndex === 2 ? 6 : (index % 7 === 0 ? 3 : 4);

    el.style.width = size + 'px';
    el.style.height = size + 'px';
    el.style.background = this.colors[colorIndex].bg;
    el.style.boxShadow = this.colors[colorIndex].shadow;

    var mote = {
        el: el,
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        baseX: 0,
        baseY: 0,
        vx: 0,
        vy: 0,
        wobbleSpeedX: 0.3 + Math.random() * 0.5,
        wobbleSpeedY: 0.2 + Math.random() * 0.4,
        wobbleAmountX: 15 + Math.random() * 25,
        wobbleAmountY: 10 + Math.random() * 20,
        phase: Math.random() * Math.PI * 2,
        phaseY: Math.random() * Math.PI * 2,
        opacity: 0,
        size: size,
        delay: Math.random() * 3000,
        born: Date.now()
    };

    mote.baseX = mote.x;
    mote.baseY = mote.y;
    this.motes.push(mote);
    this.container.appendChild(el);
};

MotesEngine.prototype.animate = function() {
    if (!this.running) return;

    var self = this;
    var now = Date.now();

    for (var i = 0; i < this.motes.length; i++) {
        var m = this.motes[i];

        if (now - m.born < m.delay) {
            m.el.style.opacity = 0;
            continue;
        }

        var age = now - m.born - m.delay;
        var wobbleX = Math.sin(age * 0.001 * m.wobbleSpeedX + m.phase) * m.wobbleAmountX;
        var wobbleY = Math.sin(age * 0.001 * m.wobbleSpeedY + m.phaseY) * m.wobbleAmountY;

        m.x = m.baseX + wobbleX;
        m.y = m.baseY + wobbleY;

        if (!this.ignoreMouse) {
            var dx = m.x - this.mouseX;
            var dy = m.y - this.mouseY;
            var dist = Math.sqrt(dx * dx + dy * dy);
            var repelRadius = 120;

            if (dist < repelRadius && dist > 0) {
                var force = (repelRadius - dist) / repelRadius;
                var angle = Math.atan2(dy, dx);
                m.vx += Math.cos(angle) * force * 0.8;
                m.vy += Math.sin(angle) * force * 0.8;
            }
            m.vx *= 0.92;
            m.vy *= 0.92;
            m.x += m.vx;
            m.y += m.vy;
        }

        var progress = Math.min(1, age / 1500);
        var pulse = 0.7 + 0.3 * Math.sin(age * 0.002 + m.phase);
        m.opacity = progress * pulse;

        m.el.style.transform = 'translate(' + m.x + 'px, ' + m.y + 'px)';
        m.el.style.opacity = m.opacity;
    }

    this.animationId = requestAnimationFrame(function() { self.animate(); });
};

MotesEngine.prototype.stop = function() {
    this.running = false;
    if (this.animationId) {
        cancelAnimationFrame(this.animationId);
    }
    if (this.mouseMoveHandler) {
        document.removeEventListener('mousemove', this.mouseMoveHandler);
    }
    if (this.container) {
        this.container.remove();
    }
    this.motes = [];
};

@php
    $snowSetting = auth()->user()->settings['snow'] ?? 'no_snow';
    $motesCount = $snowSetting === 'motes' ? 35 : 0;
    $ignoreMouse = auth()->user()->settings['snow_ignore_mouse'] ?? false;
@endphp
document.addEventListener('DOMContentLoaded', function() {
    initMotesOfLight({{ $motesCount }}, {{ $ignoreMouse ? 'true' : 'false' }});
});
</script>
@endauth
