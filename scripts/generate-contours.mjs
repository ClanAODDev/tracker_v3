import { writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const WIDTH = 2400;
const HEIGHT = 2400;
const STEP = 8;
const LEVELS = 33;
const MAJOR_EVERY = 11;
const MIN_LOOP_LENGTH = 40;
const SIMPLIFY_TOLERANCE = 0.8;
const SEED = 1999;

const THEMES = {
    dark: {
        minor: { color: '#e7e9ec', opacity: 0.0275, width: 1 },
        major: { color: '#e11d2e', opacity: 0.16, width: 2.4 },
    },
    light: {
        minor: { color: '#16181c', opacity: 0.045, width: 1 },
        major: { color: '#e11d2e', opacity: 0.14, width: 2.4 },
    },
};

function mulberry32(seed) {
    let state = seed >>> 0;
    return () => {
        state = (state + 0x6d2b79f5) >>> 0;
        let t = state;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

function createTileableNoise(random, period) {
    const gradients = Array.from({ length: period * period }, () => {
        const angle = random() * Math.PI * 2;
        return [Math.cos(angle), Math.sin(angle)];
    });
    const gradientAt = (x, y) => gradients[(((y % period) + period) % period) * period + (((x % period) + period) % period)];
    const fade = (t) => t * t * t * (t * (t * 6 - 15) + 10);
    const dot = (gx, gy, x, y) => {
        const [dx, dy] = gradientAt(gx, gy);
        return dx * (x - gx) + dy * (y - gy);
    };

    return (u, v) => {
        const x = u * period;
        const y = v * period;
        const x0 = Math.floor(x);
        const y0 = Math.floor(y);
        const sx = fade(x - x0);
        const sy = fade(y - y0);
        const top = dot(x0, y0, x, y) + sx * (dot(x0 + 1, y0, x, y) - dot(x0, y0, x, y));
        const bottom = dot(x0, y0 + 1, x, y) + sx * (dot(x0 + 1, y0 + 1, x, y) - dot(x0, y0 + 1, x, y));
        return top + sy * (bottom - top);
    };
}

function createFractalNoise(random, basePeriod, octaves) {
    const layers = Array.from({ length: octaves }, (_, octave) => ({
        noise: createTileableNoise(random, basePeriod * 2 ** octave),
        amplitude: 0.5 ** octave,
    }));

    return (u, v) => layers.reduce((sum, { noise, amplitude }) => sum + noise(u, v) * amplitude, 0);
}

function sampleHeightField() {
    const random = mulberry32(SEED);
    const warpX = createFractalNoise(random, 3, 3);
    const warpY = createFractalNoise(random, 3, 3);
    const terrain = createFractalNoise(random, 3, 3);
    const columns = WIDTH / STEP;
    const rows = HEIGHT / STEP;
    const field = [];

    for (let j = 0; j <= rows; j++) {
        const row = [];
        for (let i = 0; i <= columns; i++) {
            const u = (i % columns) / columns;
            const v = (j % rows) / rows;
            row.push(terrain(u + 0.12 * warpX(u, v), v + 0.12 * warpY(u, v)));
        }
        field.push(row);
    }

    const values = field.flat();
    const min = Math.min(...values);
    const max = Math.max(...values);

    return field.map((row) => row.map((value) => (value - min) / (max - min)));
}

const CASE_EDGES = {
    1: [['left', 'bottom']],
    2: [['bottom', 'right']],
    3: [['left', 'right']],
    4: [['top', 'right']],
    6: [['top', 'bottom']],
    7: [['left', 'top']],
    8: [['left', 'top']],
    9: [['top', 'bottom']],
    11: [['top', 'right']],
    12: [['left', 'right']],
    13: [['bottom', 'right']],
    14: [['left', 'bottom']],
};

function traceLevel(field, level) {
    const rows = field.length - 1;
    const columns = field[0].length - 1;
    const points = new Map();
    const neighbours = new Map();

    const edgePoint = (i, j, edge) => {
        const key = edge === 'top' ? `h:${i}:${j}` : edge === 'bottom' ? `h:${i}:${j + 1}` : edge === 'left' ? `v:${i}:${j}` : `v:${i + 1}:${j}`;
        if (!points.has(key)) {
            const [ax, ay, bx, by] =
                edge === 'top' ? [i, j, i + 1, j] : edge === 'bottom' ? [i, j + 1, i + 1, j + 1] : edge === 'left' ? [i, j, i, j + 1] : [i + 1, j, i + 1, j + 1];
            const a = field[ay][ax];
            const b = field[by][bx];
            const t = (level - a) / (b - a);
            points.set(key, [(ax + (bx - ax) * t) * STEP, (ay + (by - ay) * t) * STEP]);
        }
        return key;
    };

    const link = (a, b) => {
        neighbours.set(a, [...(neighbours.get(a) ?? []), b]);
        neighbours.set(b, [...(neighbours.get(b) ?? []), a]);
    };

    for (let j = 0; j < rows; j++) {
        for (let i = 0; i < columns; i++) {
            const tl = field[j][i] > level ? 8 : 0;
            const tr = field[j][i + 1] > level ? 4 : 0;
            const br = field[j + 1][i + 1] > level ? 2 : 0;
            const bl = field[j + 1][i] > level ? 1 : 0;
            const index = tl | tr | br | bl;

            if (index === 5 || index === 10) {
                const centreHigh = (field[j][i] + field[j][i + 1] + field[j + 1][i] + field[j + 1][i + 1]) / 4 > level;
                const pairs =
                    (index === 5) === centreHigh
                        ? [['left', 'top'], ['bottom', 'right']]
                        : [['left', 'bottom'], ['top', 'right']];
                pairs.forEach(([a, b]) => link(edgePoint(i, j, a), edgePoint(i, j, b)));
                continue;
            }

            (CASE_EDGES[index] ?? []).forEach(([a, b]) => link(edgePoint(i, j, a), edgePoint(i, j, b)));
        }
    }

    const visited = new Set();
    const lines = [];

    const walk = (start) => {
        const chain = [start];
        visited.add(start);
        let current = start;
        for (;;) {
            const next = (neighbours.get(current) ?? []).find((key) => !visited.has(key));
            if (!next) {
                break;
            }
            visited.add(next);
            chain.push(next);
            current = next;
        }
        return chain;
    };

    for (const [key, links] of neighbours) {
        if (!visited.has(key) && links.length === 1) {
            lines.push({ closed: false, points: walk(key).map((k) => points.get(k)) });
        }
    }
    for (const key of neighbours.keys()) {
        if (!visited.has(key)) {
            lines.push({ closed: true, points: walk(key).map((k) => points.get(k)) });
        }
    }

    return lines;
}

function simplify(points, tolerance) {
    if (points.length < 3) {
        return points;
    }
    const [ax, ay] = points[0];
    const [bx, by] = points[points.length - 1];
    const length = Math.hypot(bx - ax, by - ay) || 1;
    let furthest = 0;
    let index = 0;
    for (let k = 1; k < points.length - 1; k++) {
        const [px, py] = points[k];
        const distance = Math.abs((by - ay) * px - (bx - ax) * py + bx * ay - by * ax) / length;
        if (distance > furthest) {
            furthest = distance;
            index = k;
        }
    }
    if (furthest <= tolerance) {
        return [points[0], points[points.length - 1]];
    }
    return [...simplify(points.slice(0, index + 1), tolerance).slice(0, -1), ...simplify(points.slice(index), tolerance)];
}

function simplifyLine({ closed, points }) {
    if (!closed) {
        return { closed, points: simplify(points, SIMPLIFY_TOLERANCE) };
    }
    const half = Math.floor(points.length / 2);
    const first = simplify(points.slice(0, half + 1), SIMPLIFY_TOLERANCE);
    const second = simplify([...points.slice(half), points[0]], SIMPLIFY_TOLERANCE);
    return { closed, points: [...first.slice(0, -1), ...second.slice(0, -1)] };
}

function lineLength(points) {
    return points.slice(1).reduce((total, [x, y], k) => total + Math.hypot(x - points[k][0], y - points[k][1]), 0);
}

function toPathData(lines) {
    const midpoint = ([ax, ay], [bx, by]) => [(ax + bx) / 2, (ay + by) / 2];

    return lines
        .map(({ closed, points }) => {
            const anchors = closed ? [...points, points[0], points[1]] : points;
            const start = closed ? midpoint(anchors[0], anchors[1]) : anchors[0];
            let cursor = start.map(Math.round);
            let data = `M${cursor[0]} ${cursor[1]}`;
            const moveTo = (control, end) => {
                const c = control.map(Math.round);
                const e = end.map(Math.round);
                data += `q${c[0] - cursor[0]} ${c[1] - cursor[1]} ${e[0] - cursor[0]} ${e[1] - cursor[1]}`;
                cursor = e;
            };
            const last = closed ? anchors.length - 1 : anchors.length - 2;
            for (let k = 1; k < last; k++) {
                moveTo(anchors[k], midpoint(anchors[k], anchors[k + 1]));
            }
            if (!closed) {
                moveTo(anchors[anchors.length - 2], anchors[anchors.length - 1]);
            }
            return data + (closed ? 'z' : '');
        })
        .join('');
}

function buildContours() {
    const field = sampleHeightField();
    const minor = [];
    const major = [];

    for (let n = 1; n < LEVELS; n++) {
        const lines = traceLevel(field, n / LEVELS)
            .filter((line) => line.points.length > 2 && (!line.closed || lineLength(line.points) >= MIN_LOOP_LENGTH))
            .map(simplifyLine);
        (n % MAJOR_EVERY === 0 ? major : minor).push(...lines);
    }

    return { minor: toPathData(minor), major: toPathData(major) };
}

function renderSvg(paths, theme) {
    const stroke = ({ color, opacity, width }) =>
        `fill="none" stroke="${color}" stroke-opacity="${opacity}" stroke-width="${width}" stroke-linejoin="round" stroke-linecap="round"`;

    return (
        `<svg xmlns="http://www.w3.org/2000/svg" width="${WIDTH}" height="${HEIGHT}" viewBox="0 0 ${WIDTH} ${HEIGHT}">` +
        `<path ${stroke(theme.minor)} d="${paths.minor}"/>` +
        `<path ${stroke(theme.major)} d="${paths.major}"/>` +
        `</svg>\n`
    );
}

const outputDir = resolve(dirname(fileURLToPath(import.meta.url)), '../resources/images');
const paths = buildContours();

for (const [name, theme] of Object.entries(THEMES)) {
    const file = resolve(outputDir, `contours-${name}.svg`);
    writeFileSync(file, renderSvg(paths, theme));
    console.log(`${file} (${(renderSvg(paths, theme).length / 1024).toFixed(0)} KB)`);
}
