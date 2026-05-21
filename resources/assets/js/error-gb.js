document.addEventListener('DOMContentLoaded', function () {
    var btn     = document.getElementById('pong-btn');
    var wrapper = document.getElementById('err-pong-wrapper');
    var canvas  = document.getElementById('pong-canvas');

    if (!btn || !wrapper || !canvas) return;

    var started  = false;
    var running  = false;
    var raf;
    var audioCtx = null;

    function squawk() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var osc  = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(900, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(380, audioCtx.currentTime + 0.12);
            gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.15);
        } catch (e) {}
    }
    var update, draw;
    var onJump, onDuckStart, onDuckEnd;

    var fsBtn = document.getElementById('fs-btn');
    if (fsBtn) {
        fsBtn.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                wrapper.requestFullscreen().catch(function () {});
            } else {
                document.exitFullscreen();
            }
        });

        document.addEventListener('fullscreenchange', function () {
            fsBtn.textContent = document.fullscreenElement ? '⛶ exit' : '⛶ fullscreen';
        });
    }

    document.addEventListener('keydown', function (e) {
        if (!running) return;
        if (e.code === 'Space' || e.code === 'ArrowUp') {
            e.preventDefault();
            if (onJump) onJump();
        }
        if (e.code === 'ArrowDown') {
            e.preventDefault();
            if (onDuckStart) onDuckStart();
        }
    });

    document.addEventListener('keyup', function (e) {
        if (e.code === 'ArrowDown') {
            if (onDuckEnd) onDuckEnd();
        }
    });

    canvas.addEventListener('touchstart', function (e) {
        if (!running) return;
        e.preventDefault();
        if (onJump) onJump();
    }, { passive: false });

    canvas.addEventListener('click', function () {
        if (!running) return;
        if (onJump) onJump();
    });

    btn.addEventListener('click', function () {
        if (!wrapper.classList.contains('is-open')) {
            wrapper.classList.add('is-open');
            btn.textContent = '✕ close';
            if (!started) { initGame(); started = true; }
            running = true;
            loop();
        } else {
            wrapper.classList.remove('is-open');
            btn.textContent = '▶ grog run';
            running     = false;
            started     = false;
            update      = null;
            draw        = null;
            onJump      = null;
            onDuckStart = null;
            onDuckEnd   = null;
            cancelAnimationFrame(raf);
        }
    });

    function initGame() {
        var ctx    = canvas.getContext('2d');
        var W      = canvas.width;
        var H      = canvas.height;
        var accent = getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#f6a821';

        var GY = H - 46;

        var DX     = 56;
        var DW     = 22;
        var DH     = 46;
        var DUCK_W = 36;
        var DUCK_H = 24;

        var GRAVITY  = 0.7;
        var JUMP_VEL = -14;
        var BASE_SPD = 4;

        var hair  = accent;
        var shirt = 'rgba(235,225,205,0.92)';
        var vest  = 'rgba(100,65,25,0.9)';
        var pants = 'rgba(50,70,140,0.9)';
        var boot  = 'rgba(40,25,10,0.9)';
        var skin  = 'rgba(215,170,115,0.9)';
        var eyeC  = 'rgba(20,10,0,0.85)';
        var mugC  = 'rgba(175,105,28,0.88)';
        var woodC = 'rgba(120,75,22,0.9)';
        var goldC = 'rgba(210,170,40,0.9)';

        var gb    = { y: GY - DH, vy: 0, ducking: false, dead: false, leg: 0, legTick: 0 };
        var dockX = 0;
        var bgX   = 0;

        var speed     = BASE_SPD;
        var score     = 0;
        var hiScore   = parseInt(localStorage.getItem('guybrush_hi') || '0', 10);
        var ticks     = 0;
        var over      = false;
        var obstacles  = [];
        var pebbles    = [];
        var seagulls   = [];
        var toSpawn    = 90;
        var gullSpawn  = 220 + Math.floor(Math.random() * 280);

        for (var i = 0; i < 30; i++) {
            pebbles.push({ x: Math.random() * W, size: Math.floor(Math.random() * 2) + 1 });
        }

        var kinds = {
            mug:    { w: 16, h: 34 },
            chest:  { w: 24, h: 40 },
            mugs:   { w: 36, h: 30 },
            bottle: { w: 12, h: 30 },
            parrot: { w: 22, h: 16, fly: true },
        };

        function onGround() {
            return gb.y >= GY - (gb.ducking ? DUCK_H : DH) - 1;
        }

        function restart() {
            gb.y       = GY - DH;
            gb.vy      = 0;
            gb.dead    = false;
            gb.ducking = false;
            gb.leg     = 0;
            gb.legTick = 0;
            obstacles  = [];
            toSpawn    = 90;
            ticks      = 0;
            score      = 0;
            speed      = BASE_SPD;
            over       = false;
        }

        function jump() {
            if (over) { restart(); return; }
            if (onGround() && !gb.ducking) gb.vy = JUMP_VEL;
        }

        onJump      = jump;
        onDuckStart = function () { if (!over) gb.ducking = true; };
        onDuckEnd   = function () { gb.ducking = false; };

        function spawnObstacle() {
            if (score > 15 && Math.random() < 0.22) {
                var flyY = GY - 94 + Math.floor(Math.random() * 63);
                obstacles.push({ x: W + 10, kind: 'parrot', flyY: flyY });
                if (Math.random() < 0.45) squawk();
            } else {
                var r    = Math.random();
                var kind = r < 0.38 ? 'mug' : r < 0.65 ? 'chest' : r < 0.83 ? 'mugs' : 'bottle';
                obstacles.push({ x: W + 10, kind: kind });
            }
        }

        update = function () {
            if (over) return;

            ticks++;
            score = Math.floor(ticks / 6);
            speed = Math.min(BASE_SPD + score * 0.008, 12);
            dockX += speed;
            bgX   += speed * 0.35;

            gb.vy += GRAVITY;
            gb.y  += gb.vy;
            var floor = GY - (gb.ducking ? DUCK_H : DH);
            if (gb.y >= floor) { gb.y = floor; gb.vy = 0; }

            if (onGround()) {
                gb.legTick++;
                if (gb.legTick >= Math.max(3, Math.floor(10 - speed))) {
                    gb.leg     = 1 - gb.leg;
                    gb.legTick = 0;
                }
            }

            toSpawn--;
            if (toSpawn <= 0) {
                spawnObstacle();
                toSpawn = Math.max(40, 65 + Math.floor(Math.random() * 80) - Math.floor(speed * 4));
            }

            var gbW = gb.ducking ? DUCK_W : DW;
            var gbH = gb.ducking ? DUCK_H : DH;
            var pad = 4;

            for (var i = obstacles.length - 1; i >= 0; i--) {
                var obs = obstacles[i];
                obs.x -= speed;

                if (obs.x + kinds[obs.kind].w < 0) { obstacles.splice(i, 1); continue; }

                var kw  = kinds[obs.kind].w;
                var kh  = kinds[obs.kind].h;
                var hit = false;

                if (kinds[obs.kind].fly) {
                    hit = DX + gbW - pad > obs.x + pad &&
                          DX + pad < obs.x + kw - pad &&
                          gb.y + gbH - pad > obs.flyY + pad &&
                          gb.y + pad < obs.flyY + kh - pad;
                } else {
                    hit = DX + gbW - pad > obs.x + pad &&
                          DX + pad < obs.x + kw - pad &&
                          gb.y + gbH - pad > GY - kh;
                }

                if (hit) {
                    over = gb.dead = true;
                    if (score > hiScore) { hiScore = score; localStorage.setItem('guybrush_hi', hiScore); }
                }

            }

            gullSpawn--;
            if (gullSpawn <= 0) {
                seagulls.push({ x: W + 20, y: GY - 90 - Math.floor(Math.random() * 55), wing: 0, wingTick: 0 });
                gullSpawn = 300 + Math.floor(Math.random() * 350);
            }

            for (var g = seagulls.length - 1; g >= 0; g--) {
                seagulls[g].x -= speed * 0.55;
                seagulls[g].wingTick++;
                if (seagulls[g].wingTick >= 14) {
                    seagulls[g].wing     = 1 - seagulls[g].wing;
                    seagulls[g].wingTick = 0;
                }
                if (seagulls[g].x < -30) seagulls.splice(g, 1);
            }

            for (var j = 0; j < pebbles.length; j++) {
                pebbles[j].x -= speed;
                if (pebbles[j].x < -4) {
                    pebbles[j].x    = W + Math.random() * 20;
                    pebbles[j].size = Math.floor(Math.random() * 2) + 1;
                }
            }
        };

        function f(x, y, w, h) { ctx.fillRect(x, y, w, h); }

        function drawGuybrush() {
            var x  = DX;
            var y  = gb.y;
            var ll = gb.leg === 0;

            if (gb.dead) {
                ctx.fillStyle = 'rgba(220,70,50,0.85)';
                f(x, y, DW, DH);
                return;
            }

            if (gb.ducking) {
                ctx.fillStyle = hair;
                f(x + 18, y + 0,  16, 5);
                f(x + 16, y + 2,  4,  8);

                ctx.fillStyle = skin;
                f(x + 18, y + 3,  12, 10);

                ctx.fillStyle = eyeC;
                f(x + 27, y + 5,  3,  3);

                ctx.fillStyle = shirt;
                f(x + 2,  y + 10, 20, 10);

                ctx.fillStyle = vest;
                f(x + 6,  y + 10, 14, 10);

                ctx.fillStyle = pants;
                f(x + 2,  y + 18, 8,  6);
                f(x + 14, y + 18, 8,  6);

                ctx.fillStyle = boot;
                f(x + 0,  y + 20, 10, 4);
                f(x + 12, y + 20, 10, 4);

            } else {
                ctx.fillStyle = hair;
                f(x - 4,  y + 5,  5,  14);

                ctx.fillStyle = hair;
                f(x + 1,  y + 0,  18, 5);
                f(x + 0,  y + 3,  5,  9);
                f(x + 15, y + 3,  5,  9);

                ctx.fillStyle = skin;
                f(x + 4,  y + 4,  12, 10);

                ctx.fillStyle = eyeC;
                f(x + 13, y + 7,  3,  3);

                ctx.fillStyle = shirt;
                f(x + 1,  y + 14, 6,  10);
                f(x + 13, y + 14, 6,  10);
                f(x + 4,  y + 14, 12, 14);

                ctx.fillStyle = vest;
                f(x + 5,  y + 14, 10, 14);

                ctx.fillStyle = eyeC;
                f(x + 3,  y + 27, 14, 2);

                ctx.fillStyle = pants;
                if (!onGround()) {
                    f(x + 3,  y + 29, 6, 11);
                    f(x + 11, y + 29, 6, 11);
                } else if (ll) {
                    f(x + 3,  y + 30, 6, 10);
                    f(x + 11, y + 29, 6, 11);
                } else {
                    f(x + 3,  y + 29, 6, 11);
                    f(x + 11, y + 30, 6, 10);
                }

                ctx.fillStyle = boot;
                if (!onGround()) {
                    f(x + 2,  y + 40, 7, 6);
                    f(x + 10, y + 40, 7, 6);
                } else if (ll) {
                    f(x + 2,  y + 40, 7,  6);
                    f(x + 8,  y + 39, 10, 7);
                } else {
                    f(x + 1,  y + 39, 10, 7);
                    f(x + 10, y + 40, 7,  6);
                }
            }
        }

        function drawParrot(obs) {
            var x      = obs.x;
            var y      = obs.flyY;
            var wingUp = Math.floor(ticks / 8) % 2 === 0;

            ctx.fillStyle = 'rgba(210,40,40,0.92)';
            f(x + 4,  y + 4,  14, 8);
            f(x + 2,  y + 6,  18, 4);

            if (wingUp) {
                f(x + 2,  y + 0,  8, 5);
                f(x + 12, y + 0,  8, 5);
            } else {
                f(x + 2,  y + 10, 8, 5);
                f(x + 12, y + 10, 8, 5);
            }

            ctx.fillStyle = 'rgba(255,210,30,0.95)';
            f(x + 16, y + 5, 5, 3);

            ctx.fillStyle = 'rgba(20,10,0,0.85)';
            f(x + 15, y + 4, 3, 3);

            ctx.fillStyle = 'rgba(50,180,60,0.9)';
            f(x + 0,  y + 8, 4, 3);
            f(x + 18, y + 8, 4, 3);
        }

        function drawObstacle(obs) {
            if (obs.kind === 'parrot') { drawParrot(obs); return; }

            var x = obs.x;

            if (obs.kind === 'mug') {
                ctx.fillStyle = mugC;
                f(x + 2,  GY - 34, 12, 34);
                f(x + 0,  GY - 34, 16, 4);
                f(x + 14, GY - 24, 4,  14);
                f(x + 14, GY - 24, 4,  3);
                f(x + 14, GY - 13, 4,  3);

            } else if (obs.kind === 'chest') {
                ctx.fillStyle = woodC;
                f(x + 0,  GY - 40, 24, 40);
                ctx.fillStyle = goldC;
                f(x + 0,  GY - 40, 24,  4);
                f(x + 0,  GY - 28,  3, 28);
                f(x + 21, GY - 28,  3, 28);
                f(x + 0,  GY - 14, 24,  3);
                ctx.fillStyle = goldC;
                f(x + 10, GY - 30,  4,  6);
                f(x + 11, GY - 26,  2,  6);

            } else if (obs.kind === 'bottle') {
                var bottleC = 'rgba(108,58,14,0.88)';
                var labelC  = 'rgba(72,35,8,0.92)';
                var corkC   = 'rgba(205,175,90,0.92)';

                ctx.fillStyle = bottleC;
                f(x + 4,  GY - 30, 4,  8);
                f(x + 2,  GY - 22, 8,  3);
                f(x + 1,  GY - 19, 10, 19);

                ctx.fillStyle = labelC;
                f(x + 2,  GY - 15, 8,  8);

                ctx.fillStyle = 'rgba(160,100,40,0.3)';
                f(x + 9,  GY - 19, 2,  19);

                ctx.fillStyle = corkC;
                f(x + 4,  GY - 32, 4,  3);

            } else {
                ctx.fillStyle = mugC;
                f(x + 1,  GY - 30, 10, 30);
                f(x - 1,  GY - 30, 14,  4);
                f(x + 11, GY - 22,  4, 12);
                f(x + 20, GY - 24, 10, 24);
                f(x + 18, GY - 24, 14,  4);
                f(x + 30, GY - 18,  4, 10);
            }
        }

        var shantyWall  = 'rgba(55,38,22,0.55)';
        var shantyRoof  = 'rgba(40,26,12,0.65)';
        var shantyLight = 'rgba(220,160,40,0.35)';
        var shantyMast  = 'rgba(48,32,14,0.5)';

        var shantyBlocks = (function () {
            var rng = 1;
            function rand() { rng = (rng * 1664525 + 1013904223) & 0xffffffff; return (rng >>> 0) / 0xffffffff; }
            var blocks = [];
            var cx     = 0;
            while (cx < W * 3) {
                var kind = rand() < 0.25 ? 'mast' : rand() < 0.4 ? 'tall' : 'shack';
                if (kind === 'mast') {
                    blocks.push({ x: cx, kind: 'mast', w: 4, h: 80 + Math.floor(rand() * 30) });
                    cx += 60 + Math.floor(rand() * 40);
                } else if (kind === 'tall') {
                    var bw = 28 + Math.floor(rand() * 20);
                    var bh = 55 + Math.floor(rand() * 35);
                    blocks.push({ x: cx, kind: 'tall', w: bw, h: bh, win: rand() < 0.6 });
                    cx += bw + 4 + Math.floor(rand() * 12);
                } else {
                    var sw = 20 + Math.floor(rand() * 18);
                    var sh = 30 + Math.floor(rand() * 25);
                    blocks.push({ x: cx, kind: 'shack', w: sw, h: sh, win: rand() < 0.5 });
                    cx += sw + 2 + Math.floor(rand() * 10);
                }
            }
            return blocks;
        }());

        var SHANTY_SPAN = (function () {
            var last = shantyBlocks[shantyBlocks.length - 1];
            return last.x + last.w + 20;
        }());

        function drawShantyTown() {
            var ox = bgX % SHANTY_SPAN;

            for (var pass = 0; pass < 2; pass++) {
                var offsetX = pass === 0 ? -ox : SHANTY_SPAN - ox;

                for (var i = 0; i < shantyBlocks.length; i++) {
                    var b  = shantyBlocks[i];
                    var bx = Math.floor(b.x + offsetX);

                    if (bx + b.w < -10 || bx > W + 10) continue;

                    var by = GY - b.h;

                    if (b.kind === 'mast') {
                        ctx.fillStyle = shantyMast;
                        f(bx,      by,      b.w, b.h);
                        f(bx - 10, by + 6,  24,  2);
                        f(bx - 6,  by + 18, 16,  2);
                    } else {
                        ctx.fillStyle = shantyWall;
                        f(bx, by, b.w, b.h);

                        ctx.fillStyle = shantyRoof;
                        f(bx - 2, by - 6,  b.w + 4, 8);
                        f(bx + 2, by - 10, b.w - 4, 6);

                        if (b.win) {
                            ctx.fillStyle = shantyLight;
                            var wx = bx + Math.floor(b.w * 0.25);
                            var wy = by + Math.floor(b.h * 0.3);
                            f(wx, wy, 5, 5);
                            if (b.w > 28) f(wx + 10, wy, 5, 5);
                        }
                    }
                }
            }
        }

        function pad5(n) { return String(n).padStart(5, '0'); }

        draw = function () {
            ctx.clearRect(0, 0, W, H);

            ctx.fillStyle = 'rgba(0,0,0,0.25)';
            ctx.fillRect(0, 0, W, H);

            drawShantyTown();

            ctx.fillStyle = 'rgba(10,28,60,0.92)';
            f(0, GY + 12, W, H - GY - 12);

            ctx.fillStyle = 'rgba(55,110,185,0.2)';
            for (var i = 0; i < pebbles.length; i++) {
                f(pebbles[i].x, GY + 18 + (i % 5) * 5, pebbles[i].size * 5 + 3, 1);
            }

            ctx.fillStyle = 'rgba(68,40,14,0.95)';
            var postGap = 90;
            for (var px = W - (dockX % postGap); px > -postGap; px -= postGap) {
                f(Math.floor(px) - 4, GY + 10, 9, H - GY - 10);
            }

            ctx.fillStyle = 'rgba(122,76,32,0.92)';
            f(0, GY, W, 12);

            ctx.fillStyle = 'rgba(155,105,52,0.45)';
            f(0, GY + 1, W, 3);

            ctx.fillStyle = 'rgba(55,28,8,0.88)';
            var plankGap = 22;
            for (var gx = W - (dockX % plankGap); gx > -plankGap; gx -= plankGap) {
                f(Math.floor(gx), GY, 2, 12);
            }

            ctx.fillStyle = 'rgba(210,210,205,0.65)';
            for (var g = 0; g < seagulls.length; g++) {
                var gx = Math.floor(seagulls[g].x);
                var gy = seagulls[g].y;
                var wu = seagulls[g].wing === 0;
                f(gx + 3, gy + 2, 5, 2);
                if (wu) {
                    f(gx,     gy,     4, 2);
                    f(gx + 7, gy,     4, 2);
                } else {
                    f(gx,     gy + 3, 4, 2);
                    f(gx + 7, gy + 3, 4, 2);
                }
                ctx.fillStyle = 'rgba(210,185,90,0.65)';
                f(gx + 8, gy + 2, 2, 1);
                ctx.fillStyle = 'rgba(210,210,205,0.65)';
            }

            for (var i = 0; i < obstacles.length; i++) drawObstacle(obstacles[i]);

            drawGuybrush();

            ctx.fillStyle = 'rgba(255,255,255,0.25)';
            ctx.font      = '11px monospace';
            ctx.textAlign = 'right';
            ctx.fillText('HI ' + pad5(hiScore) + '  ' + pad5(score), W - 12, 20);

            if (over) {
                ctx.fillStyle = 'rgba(255,255,255,0.82)';
                ctx.font      = 'bold 13px monospace';
                ctx.textAlign = 'center';
                ctx.fillText("I'M GUYBRUSH THREEPWOOD", W / 2, H / 2 - 12);
                ctx.font      = '11px monospace';
                ctx.fillStyle = 'rgba(255,255,255,0.5)';
                ctx.fillText("and I'm dead.", W / 2, H / 2 + 4);
                ctx.font      = '10px monospace';
                ctx.fillStyle = 'rgba(255,255,255,0.28)';
                ctx.fillText('space / click / tap to try again', W / 2, H / 2 + 20);
            }
        };
    }

    function loop() {
        if (!running) return;
        if (update) { update(); draw(); }
        raf = requestAnimationFrame(loop);
    }
});
