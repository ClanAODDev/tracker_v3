document.addEventListener('DOMContentLoaded', function () {
    var btn     = document.getElementById('pong-btn');
    var wrapper = document.getElementById('err-pong-wrapper');

    if (!btn || !wrapper) return;

    var started = false;
    var running = false;
    var raf;

    btn.addEventListener('click', function () {
        if (!wrapper.classList.contains('is-open')) {
            wrapper.classList.add('is-open');
            btn.textContent = '✕ close pong';
            if (!started) { initPong(); started = true; }
            running = true;
            loop();
        } else {
            wrapper.classList.remove('is-open');
            btn.textContent = '▶ play pong';
            running = false;
            started = false;
            update  = null;
            draw    = null;
            cancelAnimationFrame(raf);
        }
    });

    var update, draw;

    function initPong() {
        var canvas = document.getElementById('pong-canvas');
        var ctx    = canvas.getContext('2d');
        var W      = canvas.width;
        var H      = canvas.height;
        var accent = getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#f6a821';

        var PW = 8, PH = 56, BALL = 7;
        var player = { y: H / 2 - PH / 2, score: 0 };
        var ai     = { y: H / 2 - PH / 2, score: 0 };
        var ball   = {};
        var mouseY = H / 2;

        function resetBall(dir) {
            ball.x  = W / 2;
            ball.y  = H / 2;
            ball.vx = (dir || 1) * (3 + Math.random());
            ball.vy = (Math.random() * 3 - 1.5);
        }
        resetBall(1);

        canvas.addEventListener('mousemove', function (e) {
            mouseY = e.clientY - canvas.getBoundingClientRect().top;
        });

        canvas.addEventListener('touchmove', function (e) {
            e.preventDefault();
            mouseY = e.touches[0].clientY - canvas.getBoundingClientRect().top;
        }, { passive: false });

        update = function () {
            player.y = Math.max(0, Math.min(H - PH, mouseY - PH / 2));

            var aiMid = ai.y + PH / 2;
            var aiSpd = 3.2;
            if (aiMid < ball.y - 4) ai.y = Math.min(H - PH, ai.y + aiSpd);
            else if (aiMid > ball.y + 4) ai.y = Math.max(0, ai.y - aiSpd);

            ball.x += ball.vx;
            ball.y += ball.vy;

            if (ball.y <= 0)        { ball.y = 0;        ball.vy = Math.abs(ball.vy); }
            if (ball.y >= H - BALL) { ball.y = H - BALL; ball.vy = -Math.abs(ball.vy); }

            var spd = Math.sqrt(ball.vx * ball.vx + ball.vy * ball.vy);
            if (spd > 9) { ball.vx = ball.vx / spd * 9; ball.vy = ball.vy / spd * 9; }

            if (ball.x <= 20 + PW && ball.x >= 20 && ball.y + BALL >= player.y && ball.y <= player.y + PH) {
                ball.vx  = Math.abs(ball.vx) * 1.05;
                ball.vy += (ball.y + BALL / 2 - (player.y + PH / 2)) * 0.12;
            }

            if (ball.x + BALL >= W - 20 - PW && ball.x + BALL <= W - 20 && ball.y + BALL >= ai.y && ball.y <= ai.y + PH) {
                ball.vx  = -Math.abs(ball.vx) * 1.05;
                ball.vy += (ball.y + BALL / 2 - (ai.y + PH / 2)) * 0.12;
            }

            if (ball.x < -BALL) { ai.score++;     resetBall(1);  }
            if (ball.x > W)     { player.score++; resetBall(-1); }
        };

        draw = function () {
            ctx.clearRect(0, 0, W, H);

            ctx.fillStyle = 'rgba(0,0,0,0.25)';
            ctx.fillRect(0, 0, W, H);

            ctx.setLineDash([5, 10]);
            ctx.strokeStyle = 'rgba(255,255,255,0.06)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(W / 2, 0);
            ctx.lineTo(W / 2, H);
            ctx.stroke();
            ctx.setLineDash([]);

            ctx.fillStyle = accent;
            ctx.fillRect(20, player.y, PW, PH);
            ctx.fillRect(W - 20 - PW, ai.y, PW, PH);
            ctx.fillRect(ball.x, ball.y, BALL, BALL);

            ctx.fillStyle = 'rgba(255,255,255,0.2)';
            ctx.font = '13px monospace';
            ctx.textAlign = 'left';
            ctx.fillText(player.score, W / 2 - 30, 18);
            ctx.textAlign = 'right';
            ctx.fillText(ai.score, W / 2 + 30, 18);
        };
    }

    function loop() {
        if (!running) return;
        if (update) { update(); draw(); }
        raf = requestAnimationFrame(loop);
    }
});
