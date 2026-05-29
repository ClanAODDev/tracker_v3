import { launchGrogGame } from './grog-game.js';

document.addEventListener('DOMContentLoaded', function () {
    var btn     = document.getElementById('pong-btn');
    var wrapper = document.getElementById('err-pong-wrapper');
    var canvas  = document.getElementById('pong-canvas');

    if (!btn || !wrapper || !canvas) return;

    var game = null;

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
        if (!game) return;
        if (e.code === 'Space' || e.code === 'ArrowUp') { e.preventDefault(); game.jump(); }
        if (e.code === 'ArrowDown') { e.preventDefault(); game.duckStart(); }
    });

    document.addEventListener('keyup', function (e) {
        if (!game) return;
        if (e.code === 'ArrowDown') game.duckEnd();
    });

    canvas.addEventListener('touchstart', function (e) {
        if (!game) return;
        e.preventDefault();
        game.jump();
    }, { passive: false });

    canvas.addEventListener('click', function () {
        if (!game) return;
        game.jump();
    });

    btn.addEventListener('click', function () {
        if (!wrapper.classList.contains('is-open')) {
            wrapper.classList.add('is-open');
            btn.textContent = '✕ close';
            game = launchGrogGame(canvas);
            game.start();
        } else {
            wrapper.classList.remove('is-open');
            btn.textContent = '▶ grog run';
            game.stop();
            game = null;
        }
    });
});
