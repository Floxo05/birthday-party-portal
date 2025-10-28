// —— Kontext & Konfiguration ——
const DEFAULTS = {
    callbackUrl: null,
    token: null,
    language: 'de',
    maxDurationMs: 5 * 60_000,
    requireVisibility: true,
    backoff: {attempts: 4, baseMs: 500}
};
window.GAME_CONFIG = window.GAME_CONFIG || {};

function getCtx() {
    const u = new URL(location.href);
    const qp = Object.fromEntries(u.searchParams.entries());
    const match = u.pathname.match(/\/public\/game\/([^\/?#]+)/);
    const gameIdFromPath = match ? decodeURIComponent(match[1]) : null;
    return {
        partyId: qp.partyId || null,
        playerId: qp.playerId || null,
        gameId: qp.gameId || gameIdFromPath || 'snake',
        sessionId: qp.sessionId || (crypto.randomUUID?.() ?? String(Date.now())),
        callbackUrl: qp.callbackUrl || window.GAME_CONFIG.callbackUrl || null,
        token: qp.token || window.GAME_CONFIG.token || null,
        lang: qp.lang || window.GAME_CONFIG.language || DEFAULTS.language
    };
}

const CTX = getCtx();
const isFileOrigin = (location.origin === 'null' || location.protocol === 'file:');

function normalizeCallbackUrl(url) {
    if (!url) return url;
    try {
        if (location.protocol === 'https:' && typeof url === 'string' && url.startsWith('http://')) {
            return 'https://' + url.slice('http://'.length);
        }
    } catch (_) {
    }
    return url;
}
const CFG = {
    callbackUrl: normalizeCallbackUrl(CTX.callbackUrl) || (isFileOrigin ? null : `${location.origin}/api/game-results`),
    token: CTX.token || null,
    language: CTX.lang,
    maxDurationMs: window.GAME_CONFIG.maxDurationMs ?? DEFAULTS.maxDurationMs,
    requireVisibility: window.GAME_CONFIG.requireVisibility ?? DEFAULTS.requireVisibility,
    backoff: window.GAME_CONFIG.backoff || DEFAULTS.backoff
};

document.getElementById('ctx').textContent = [`party:${CTX.partyId?.slice(0, 8) || '—'}`, `player:${CTX.playerId?.slice(0, 8) || '—'}`, `game:${CTX.gameId}`].join(' • ');

// —— Canvas & Grid (responsive & crisp) ——
const canvas = document.getElementById('game');
const g = canvas.getContext('2d');
// Grid sizing and speed tuning
let GRID_TARGET = 16; // dynamic cells per side (responsive)
const BASE_TICK = 300; // ms per step at base difficulty (slower overall)
let W = 0, H = 0, CELL = 24, COLS = 0, ROWS = 0;

// —— Optional Assets (images) ——
// Place images in ./assets next to this script. Filenames:
//  - snake-head-1.png (optional, default head)
//  - snake-head-2.png (optional, switches at score >= 10)
//  - snake-body.png   (optional)
//  - fruit.png        (optional)
const ASSETS = {
    basePath: 'assets/',
    head1: {img: null, ok: false},
    head2: {img: null, ok: false},
    body: {img: null, ok: false},
    fruit: {img: null, ok: false}
};
function loadImg(src, target) {
    const img = new Image();
    img.decoding = 'async';
    img.onload = () => { target.ok = true; };
    img.onerror = () => { target.ok = false; };
    img.src = src;
    target.img = img;
}
function preloadAssets() {
    try {
        loadImg(ASSETS.basePath + 'snake-head-1.png', ASSETS.head1);
        loadImg(ASSETS.basePath + 'snake-head-2.png', ASSETS.head2);
        loadImg(ASSETS.basePath + 'snake-body.png', ASSETS.body);
        loadImg(ASSETS.basePath + 'fruit.png', ASSETS.fruit);
    } catch (_) { /* ignore */ }
}
preloadAssets();

// Helpers for responsive grid sizing
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
function isMobileLike() {
    const w = window.innerWidth;
    const h = window.innerHeight;
    return w <= 820 || Math.min(w, h) <= 480;
}
function getForcedCellsFromQuery() {
    try {
        const u = new URL(location.href);
        const c = Number(u.searchParams.get('cells'));
        // Enforce at least 12x12 as requested, even when overridden
        if (Number.isFinite(c)) return clamp(Math.round(c), 12, 24);
    } catch (_) {}
    return null;
}
function computeGridTarget() {
    const forced = getForcedCellsFromQuery();
    if (forced) return forced;
    // Fixed default grid size as requested
    return 12;
}

function sizeBoard() {
    // Measure available square using the actual layout box and visual viewport (mobile friendly)
    const wrap = canvas.parentElement || document.body;
    // Let CSS control responsiveness: width:100%, aspect-ratio:1/1; then measure the real box
    // Ensure the element has layout before measuring
    const vv = window.visualViewport;
    const viewportH = Math.floor(vv ? vv.height : window.innerHeight);

    // Compute available width from the wrapper and available height from viewport minus UI chrome
    const availW = Math.max(240, Math.floor(wrap.clientWidth || window.innerWidth));
    // Estimate UI taken space (header/footer/paddings). Use actual HUD height if available.
    const hud = document.querySelector('.hud');
    const hudH = hud ? hud.offsetHeight : 0;
    const safety = 100; // extra space for headers/footers/buttons
    const availH = Math.max(240, viewportH - hudH - safety);

    // Target size of the square canvas in CSS pixels
    const S = Math.floor(Math.min(availW, availH));

    // Decide number of cells from pixel side length; when running, keep existing grid to avoid jumps
    if (state.running && COLS > 0 && ROWS > 0) {
        GRID_TARGET = COLS; // keep
    } else {
        GRID_TARGET = computeGridTarget(S);
    }

    // Device pixel ratio and backing store sizing
    const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));

    // Keep CSS responsive (width:100% set in CSS). Only adjust the explicit inline height to keep square in older browsers.
    canvas.style.height = S + 'px';

    // Backing store size snapped to full cells for crisp grid
    const targetDeviceSide = Math.floor(S * dpr);
    CELL = Math.floor(targetDeviceSide / GRID_TARGET);
    const side = CELL * GRID_TARGET;
    canvas.width = side;
    canvas.height = side;
    W = canvas.width;
    H = canvas.height;
    COLS = GRID_TARGET;
    ROWS = GRID_TARGET;
}

// —— Game State ——
const state = {
    startedAt: null,
    endedAt: null,
    over: false,
    paused: false,
    running: false,
    score: 0,
    best: Number(localStorage.getItem(`snake_highscore_${CTX.gameId}`) || 0),
    tickMs: BASE_TICK,
    tAccum: 0,
    dir: {x: 1, y: 0},
    nextDir: {x: 1, y: 0},
    snake: [],
    apples: [],
    eatAnimMs: 0
};
document.getElementById('best').textContent = String(state.best);

function reset() {
    state.over = false;
    state.paused = false;
    state.score = 0;
    state.tickMs = BASE_TICK;
    state.tAccum = 0;
    state.dir = {x: 1, y: 0};
    state.nextDir = {x: 1, y: 0};
    sizeBoard();
    const startX = Math.floor(COLS / 3), startY = Math.floor(ROWS / 2);
    // initialize with per-cell orientation (dirX/dirY) pointing to the right
    state.snake = [
        {x: startX - 2, y: startY, dirX: 1, dirY: 0},
        {x: startX - 1, y: startY, dirX: 1, dirY: 0},
        {x: startX, y: startY, dirX: 1, dirY: 0}
    ];
    state.apples = [];
    ensureAppleCount();
    document.getElementById('score').textContent = '0';
    document.getElementById('diff').textContent = `Geschwindigkeit: ${(BASE_TICK / state.tickMs).toFixed(1)}×`;
}

function targetFruits(score = state.score) {
    return score >= 10 ? 2 : 1;
}

function randFreeCell(occupiedSet) {
    // Try a bounded number of attempts to find a free cell
    const maxTries = COLS * ROWS;
    for (let i = 0; i < maxTries; i++) {
        const x = Math.floor(Math.random() * COLS);
        const y = Math.floor(Math.random() * ROWS);
        const k = `${x},${y}`;
        if (!occupiedSet.has(k)) return {x, y};
    }
    // Fallback: linear scan
    for (let y = 0; y < ROWS; y++) {
        for (let x = 0; x < COLS; x++) {
            const k = `${x},${y}`;
            if (!occupiedSet.has(k)) return {x, y};
        }
    }
    return null; // no space
}

function ensureAppleCount() {
    // Build occupied set from snake and current apples
    const occ = new Set(state.snake.map(s => `${s.x},${s.y}`));
    for (const a of state.apples) occ.add(`${a.x},${a.y}`);
    const need = targetFruits() - state.apples.length;
    for (let i = 0; i < need; i++) {
        const pos = randFreeCell(occ);
        if (!pos) break;
        state.apples.push(pos);
        occ.add(`${pos.x},${pos.y}`);
    }
}

function difficulty() {
    const sF = Math.min(state.score / 10, 2.0);
    const tMin = state.startedAt ? Math.min((Date.now() - state.startedAt.getTime()) / 60000, 3) : 0;
    // Gentler ramp to keep the game slower overall
    const f = 1 + sF * 0.35 + tMin * 0.25;
    state.tickMs = Math.max(120, BASE_TICK / f);
    document.getElementById('diff').textContent = `Geschwindigkeit: ${(BASE_TICK / state.tickMs).toFixed(1)}×`;
}

function update() {
    // decay eat animation timer
    state.eatAnimMs = Math.max(0, state.eatAnimMs - state.tickMs);

    const nd = state.nextDir, d = state.dir;
    if (nd.x !== -d.x || nd.y !== -d.y) state.dir = {x: nd.x, y: nd.y};
    const head = state.snake[state.snake.length - 1];
    const nx = head.x + state.dir.x, ny = head.y + state.dir.y;
    if (nx < 0 || nx >= COLS || ny < 0 || ny >= ROWS) return gameOver();
    for (let i = 0; i < state.snake.length - 1; i++) {
        const s = state.snake[i];
        if (s.x === nx && s.y === ny) return gameOver();
    }
    // push new head with orientation of the current direction
    state.snake.push({x: nx, y: ny, dirX: state.dir.x, dirY: state.dir.y});
    // check fruit collision (any apple)
    const hitIdx = state.apples.findIndex(a => a.x === nx && a.y === ny);
    if (hitIdx !== -1) {
        state.score++;
        state.eatAnimMs = 220; // trigger mouth-open animation
        document.getElementById('score').textContent = String(state.score);
        // remove eaten apple and ensure target number exists (>=10 => 2 fruits)
        state.apples.splice(hitIdx, 1);
        ensureAppleCount();
    } else {
        state.snake.shift();
    }
    difficulty();
}

function draw() {
    const styles = getComputedStyle(document.documentElement);
    // board bg
    g.fillStyle = styles.getPropertyValue('--board');
    g.fillRect(0, 0, W, H);
    // grid
    g.strokeStyle = styles.getPropertyValue('--grid');
    g.lineWidth = 1;
    g.beginPath();
    for (let x = 0; x <= COLS; x++) {
        g.moveTo(x * CELL, 0);
        g.lineTo(x * CELL, H);
    }
    for (let y = 0; y <= ROWS; y++) {
        g.moveTo(0, y * CELL);
        g.lineTo(W, y * CELL);
    }
    g.stroke();
    // apples (can be 1 or 2 depending on score)
    for (const a of state.apples) {
        if (ASSETS.fruit.ok && ASSETS.fruit.img) {
            g.imageSmoothingEnabled = false;
            g.drawImage(ASSETS.fruit.img, a.x * CELL, a.y * CELL, CELL, CELL);
        } else {
            const ax = a.x * CELL + CELL / 2, ay = a.y * CELL + CELL / 2,
                r = Math.max(6, CELL / 2 - 3);
            g.fillStyle = styles.getPropertyValue('--apple');
            g.beginPath();
            g.arc(ax, ay, r, 0, Math.PI * 2);
            g.fill();
            g.fillStyle = '#7c3f00';
            g.fillRect(ax - 2, ay - r - 6, 4, 8);
            g.fillStyle = '#16a34a';
            g.beginPath();
            g.moveTo(ax + 3, ay - r - 4);
            g.quadraticCurveTo(ax + 10, ay - r - 10, ax + 14, ay - r - 2);
            g.quadraticCurveTo(ax + 8, ay - r + 1, ax + 3, ay - r - 4);
            g.fill();
        }
    }
    // snake body + tail/head with images
    g.fillStyle = styles.getPropertyValue('--snake');
    const rr = (x, y, w, h, r) => {
        g.beginPath();
        g.moveTo(x + r, y);
        g.arcTo(x + w, y, x + w, y + h, r);
        g.arcTo(x + w, y + h, x, y + h, r);
        g.arcTo(x, y + h, x, y, r);
        g.arcTo(x, y, x + w, y, r);
        g.closePath();
        g.fill();
    };

    const drawHeadSpriteAt = (cx, cy, dirX, dirY, img) => {
        // sprite is facing LEFT by default
        g.imageSmoothingEnabled = false;
        g.save();
        g.translate(cx, cy);
        if (dirX > 0) {
            // RIGHT: mirror horizontally
            g.scale(-1, 1);
        } else if (dirX < 0) {
            // LEFT: no rotation
            // nothing
        } else if (dirY < 0) {
            // UP: rotate +90° (swapped)
            g.rotate(Math.PI / 2);
        } else if (dirY > 0) {
            // DOWN: rotate -90° (swapped)
            g.rotate(-Math.PI / 2);
        }
        g.drawImage(img, -CELL / 2, -CELL / 2, CELL, CELL);
        g.restore();
    };

    // Draw all body segments including tail (but excluding the head), using body sprite or vector
    for (let i = 0; i < state.snake.length - 1; i++) {
        const s = state.snake[i];
        if (ASSETS.body.ok && ASSETS.body.img) {
            g.imageSmoothingEnabled = false;
            g.drawImage(ASSETS.body.img, s.x * CELL, s.y * CELL, CELL, CELL);
        } else {
            rr(s.x * CELL + 1, s.y * CELL + 1, CELL - 2, CELL - 2, Math.min(6, CELL / 3));
        }
    }

    // head (image or vector fallback)
    const head = state.snake[state.snake.length - 1];
    if (head) {
        const hx = head.x * CELL, hy = head.y * CELL;
        const dx = head.dirX ?? state.dir.x, dy = head.dirY ?? state.dir.y;
        // Choose head sprite depending on score threshold (>=10 -> head2)
        const useHead2 = state.score >= 10 && ASSETS.head2.ok && ASSETS.head2.img;
        const headImg = useHead2 ? ASSETS.head2.img : (ASSETS.head1.ok ? ASSETS.head1.img : null);
        if (headImg) {
            const cx = hx + CELL / 2, cy = hy + CELL / 2;
            drawHeadSpriteAt(cx, cy, dx, dy, headImg);
        } else {
            const boardCol = styles.getPropertyValue('--board');
            const cx = hx + CELL / 2, cy = hy + CELL / 2;
            // base square
            rr(hx + 1, hy + 1, CELL - 2, CELL - 2, Math.min(6, CELL / 3));
            // Outline
            g.strokeStyle = 'rgba(0,0,0,.35)';
            g.lineWidth = 2;
            g.strokeRect(hx + 1, hy + 1, CELL - 2, CELL - 2);
            // Eyes
            const eyeR = Math.max(2, Math.floor(CELL / 10));
            let ex1, ey1, ex2, ey2;
            if (Math.abs(dx) > 0) {
                const offX = dx > 0 ? CELL * 0.25 : CELL * 0.75;
                ex1 = hx + offX;
                ey1 = hy + CELL * 0.35;
                ex2 = hx + offX;
                ey2 = hy + CELL * 0.65;
            } else {
                const offY = dy > 0 ? CELL * 0.25 : CELL * 0.75;
                ex1 = hx + CELL * 0.35;
                ey1 = hy + offY;
                ex2 = hx + CELL * 0.65;
                ey2 = hy + offY;
            }
            g.fillStyle = '#fff';
            g.beginPath();
            g.arc(ex1, ey1, eyeR, 0, Math.PI * 2);
            g.fill();
            g.beginPath();
            g.arc(ex2, ey2, eyeR, 0, Math.PI * 2);
            g.fill();
            g.fillStyle = '#000';
            g.beginPath();
            g.arc(ex1, ey1, eyeR * 0.5, 0, Math.PI * 2);
            g.fill();
            g.beginPath();
            g.arc(ex2, ey2, eyeR * 0.5, 0, Math.PI * 2);
            g.fill();
            // Mouth wedge in facing direction (opens on eat)
            const openT = Math.min(1, state.eatAnimMs / 220);
            if (openT > 0) {
                const ang = (dx !== 0 ? (dx > 0 ? 0 : Math.PI) : (dy > 0 ? Math.PI / 2 : -Math.PI / 2));
                const maxAngle = Math.PI * 0.6; // 108°
                const a1 = ang - maxAngle * openT * 0.5;
                const a2 = ang + maxAngle * openT * 0.5;
                const r = CELL * 0.7;
                const p1 = {x: cx + Math.cos(a1) * r, y: cy + Math.sin(a1) * r};
                const p2 = {x: cx + Math.cos(a2) * r, y: cy + Math.sin(a2) * r};
                g.fillStyle = boardCol;
                g.beginPath();
                g.moveTo(cx, cy);
                g.lineTo(p1.x, p1.y);
                g.lineTo(p2.x, p2.y);
                g.closePath();
                g.fill();
                if (openT > 0.5) {
                    g.strokeStyle = '#ef4444';
                    g.lineWidth = Math.max(2, CELL / 12);
                    g.beginPath();
                    g.moveTo(cx, cy);
                    g.lineTo(cx + Math.cos(ang) * CELL * 0.45, cy + Math.sin(ang) * CELL * 0.45);
                    g.stroke();
                }
            }
        }
    }
}

let lastTs = 0, rafId;

function loop(ts) {
    if (!lastTs) lastTs = ts;
    const dt = ts - lastTs;
    lastTs = ts;
    if (!state.over && !state.paused) {
        state.tAccum += dt;
        while (state.tAccum >= state.tickMs) {
            update();
            state.tAccum -= state.tickMs;
        }
        draw();
    }
    rafId = requestAnimationFrame(loop);
}

function start() {
    reset();
    state.startedAt = new Date();
    state.running = true;
    document.body.classList.add('playing');
    document.getElementById('startLayer').classList.add('hidden');
    document.getElementById('gameOverLayer').classList.add('hidden');
    cancelAnimationFrame(rafId);
    lastTs = 0;
    rafId = requestAnimationFrame(loop);
}

function gameOver() {
    if (state.over) return;
    state.over = true;
    state.running = false;
    document.body.classList.remove('playing');
    state.endedAt = new Date();
    if (state.score > state.best) {
        state.best = state.score;
        localStorage.setItem(`snake_highscore_${CTX.gameId}`, String(state.best));
    }
    document.getElementById('best').textContent = String(state.best);
    showSummaryAndSubmit();
}

function setDir(x, y) {
    state.nextDir = {x, y};
}

// Keyboard
const keymap = {
    ArrowUp: [0, -1],
    KeyW: [0, -1],
    ArrowDown: [0, 1],
    KeyS: [0, 1],
    ArrowLeft: [-1, 0],
    KeyA: [-1, 0],
    ArrowRight: [1, 0],
    KeyD: [1, 0]
};
document.addEventListener('keydown', e => {
    const v = keymap[e.code];
    if (!v) return;
    e.preventDefault();
    if (!state.running) return;
    setDir(v[0], v[1]);
});

// Touch swipe (scroll nur im Spiel blockieren)
let touchStart = null;
canvas.addEventListener('touchstart', e => {
    if (!state.running) return;
    if (e.touches[0]) touchStart = {x: e.touches[0].clientX, y: e.touches[0].clientY};
    e.preventDefault();
}, {passive: false});
canvas.addEventListener('touchend', e => {
    if (!state.running) return;
    if (!touchStart) return;
    const t = e.changedTouches[0];
    const dx = t.clientX - touchStart.x, dy = t.clientY - touchStart.y;
    const adx = Math.abs(dx), ady = Math.abs(dy);
    if (Math.max(adx, ady) > 20) {
        if (adx > ady) setDir(dx > 0 ? 1 : -1, 0); else setDir(0, dy > 0 ? 1 : -1);
    }
    touchStart = null;
    e.preventDefault();
}, {passive: false});

// Start/Again buttons (robust on mobile)
const startBtn = document.getElementById('startBtn');

function safeStart(ev) {
    ev?.preventDefault?.();
    ev?.stopPropagation?.();
    start();
}

startBtn.addEventListener('click', safeStart);
startBtn.addEventListener('pointerup', safeStart);
startBtn.addEventListener('touchend', safeStart, {passive: false});
document.getElementById('againBtn').addEventListener('click', safeStart);

// Pause bei Tab-Verlust nur im Spiel
document.addEventListener('visibilitychange', () => {
    if (document.hidden && CFG.requireVisibility) {
        state.paused = true;
    } else if (state.running) {
        state.paused = false;
    }
});

// —— Ergebnis-Submit (deaktiviert im file:// Modus) ——
async function postResult(url, token, payload) {
    if (!url) {
        return {status: 'ok', local: true};
    }
    const res = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', ...(token ? {Authorization: `Bearer ${token}`} : {})},
        body: JSON.stringify(payload),
        keepalive: true
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json().catch(() => ({status: 'ok'}));
}

const sleep = ms => new Promise(r => setTimeout(r, ms));

async function showSummaryAndSubmit() {
    cancelAnimationFrame(rafId);
    const layer = document.getElementById('gameOverLayer');
    const kv = document.getElementById('summary');
    kv.innerHTML = '';
    const payload = {
        partyId: CTX.partyId,
        playerId: CTX.playerId,
        gameId: CTX.gameId,
        sessionId: CTX.sessionId,
        score: state.score,
        maxScore: 9999,
        completed: true,
        durationMs: state.endedAt - state.startedAt,
        startedAt: state.startedAt.toISOString(),
        endedAt: state.endedAt.toISOString(),
        attempt: 1,
        metadata: {best: state.best, speed: (BASE_TICK / state.tickMs)},
        clientInfo: {userAgent: navigator.userAgent, viewport: {w: innerWidth, h: innerHeight}}
    };
    for (const [k, v] of [['score', payload.score], ['highscore', state.best], ['dauerMs', payload.durationMs], ['startedAt', payload.startedAt], ['endedAt', payload.endedAt]]) {
        const a = document.createElement('div');
        a.textContent = k;
        const b = document.createElement('div');
        b.textContent = String(v);
        kv.append(a, b);
    }

    const submitState = document.getElementById('submitState');
    const errBox = document.getElementById('errBox');
    errBox.classList.add('hidden');
    errBox.textContent = '';
    layer.classList.remove('hidden');

    if (isFileOrigin && !CFG.callbackUrl) {
        submitState.textContent = 'Lokaler Testmodus – kein Submit';
        return;
    }

    let ok = false, lastErr = null;
    const {attempts, baseMs} = CFG.backoff;
    let i = 0;
    while (i < attempts) {
        try {
            submitState.textContent = i ? `Sende… Versuch ${i + 1}/${attempts}` : 'Sende Ergebnis…';
            const res = await postResult(CFG.callbackUrl, CFG.token, payload);
            submitState.textContent = 'Ergebnis gespeichert.';
            ok = true;
            if (res && res.scoreboardUrl) {
                const btn = document.getElementById('scoreboardBtn');
                btn.hidden = false;
                btn.onclick = () => {
                    open(res.scoreboardUrl, '_blank');
                };
            }
            break;
        } catch (e) {
            lastErr = e;
            i++;
            if (i < attempts) await sleep(baseMs * (2 ** i));
        }
    }
    if (!ok) {
        submitState.textContent = 'Speichern fehlgeschlagen.';
        errBox.textContent = lastErr?.message || 'Unbekannter Fehler';
        errBox.classList.remove('hidden');
    }
}

// Init
(function init() {
    document.getElementById('startLayer').classList.remove('hidden');
    document.getElementById('gameOverLayer').classList.add('hidden');
    sizeBoard();
    draw();
})();

// Reflow on rotate/resize
window.addEventListener('resize', () => {
    if (!state.running) {
        sizeBoard();
        draw();
    }
});
