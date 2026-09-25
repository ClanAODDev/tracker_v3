import { useEffect, useRef } from 'react';

interface Dot {
    x: number;
    y: number;
    vx: number;
    vy: number;
}

const CONNECT_DISTANCE = 130;
const CURSOR_RADIUS = 150;
const DOT_COLOR = 'rgba(255, 255, 255, 0.6)';
const LINE_COLOR = '225, 29, 46';

/** Login-only decorative background: a field of drifting dots connected by fading
 * lines, brightening and scattering near the cursor. Freezes to a static frame under
 * `prefers-reduced-motion` instead of animating. */
export function LoginConstellation() {
    const canvasRef = useRef<HTMLCanvasElement>(null);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) {
            return;
        }
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const mouse = { x: -9999, y: -9999 };
        let dots: Dot[] = [];
        let width = 0;
        let height = 0;
        let frame = 0;

        function resize() {
            if (!canvas) {
                return;
            }
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            const count = Math.min(70, Math.floor((width * height) / 18000));
            dots = Array.from({ length: count }, () => ({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.25,
                vy: (Math.random() - 0.5) * 0.25,
            }));
        }

        function draw() {
            if (!ctx) {
                return;
            }
            ctx.clearRect(0, 0, width, height);

            for (const dot of dots) {
                const dx = dot.x - mouse.x;
                const dy = dot.y - mouse.y;
                const dist = Math.hypot(dx, dy);
                if (dist < CURSOR_RADIUS) {
                    const force = (CURSOR_RADIUS - dist) / CURSOR_RADIUS;
                    dot.x += (dx / (dist || 1)) * force * 1.5;
                    dot.y += (dy / (dist || 1)) * force * 1.5;
                }
            }

            for (let i = 0; i < dots.length; i++) {
                for (let j = i + 1; j < dots.length; j++) {
                    const a = dots[i];
                    const b = dots[j];
                    const dist = Math.hypot(a.x - b.x, a.y - b.y);
                    if (dist >= CONNECT_DISTANCE) {
                        continue;
                    }
                    const nearCursor = Math.min(Math.hypot(a.x - mouse.x, a.y - mouse.y), Math.hypot(b.x - mouse.x, b.y - mouse.y));
                    const boost = nearCursor < CURSOR_RADIUS ? 0.3 : 0;
                    const opacity = (1 - dist / CONNECT_DISTANCE) * 0.55 + boost;
                    ctx.strokeStyle = `rgba(${LINE_COLOR}, ${opacity})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }

            ctx.fillStyle = DOT_COLOR;
            for (const dot of dots) {
                ctx.beginPath();
                ctx.arc(dot.x, dot.y, 1.5, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function step() {
            for (const dot of dots) {
                dot.x += dot.vx;
                dot.y += dot.vy;
                if (dot.x < 0 || dot.x > width) {
                    dot.vx *= -1;
                }
                if (dot.y < 0 || dot.y > height) {
                    dot.vy *= -1;
                }
            }
            draw();
            frame = requestAnimationFrame(step);
        }

        function handleMouseMove(event: MouseEvent) {
            mouse.x = event.clientX;
            mouse.y = event.clientY;
        }

        resize();
        window.addEventListener('resize', resize);

        if (reduceMotion) {
            draw();
        } else {
            window.addEventListener('mousemove', handleMouseMove);
            frame = requestAnimationFrame(step);
        }

        return () => {
            window.removeEventListener('resize', resize);
            window.removeEventListener('mousemove', handleMouseMove);
            cancelAnimationFrame(frame);
        };
    }, []);

    return <canvas ref={canvasRef} aria-hidden className="pointer-events-none fixed inset-0 z-0" />;
}
