// ===== Context & Config =====
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
    const m = u.pathname.match(/\/public\/game\/([^\/?#]+)/);
    const gameIdFromPath = m ? decodeURIComponent(m[1]) : null;
    return {
        partyId: qp.partyId || null,
        playerId: qp.playerId || null,
        gameId: qp.gameId || gameIdFromPath || 'tetris',
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
const LS_KEY = `tetris_highscore_${CTX.gameId}`;

// ===== Board & Rendering =====
const CAN = document.getElementById('board');
const CTX2 = CAN.getContext('2d');
let BW = 10, BH = 20; // logical columns/rows
let CELL = 24, W = 0, H = 0, dpr = 1;

function resizeBoard() {
    // Compute integer CELL size so that BW x BH cells exactly fit into the available area
    const wrap = document.querySelector('.boardWrap');
    if (!wrap) return;
    const rect = wrap.getBoundingClientRect();
    const availW = Math.max(0, Math.floor(rect.width));
    const availH = Math.max(0, Math.floor(rect.height));
    // Determine the maximum whole-pixel CELL that fits both width and height
    dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
    const cellFromW = Math.floor(availW / BW);
    const cellFromH = Math.floor(availH / BH);
    let cellCss = Math.min(cellFromW, cellFromH);
    if (!isFinite(cellCss) || cellCss < 1) cellCss = 1; // never overflow available area

    // Compute integer device-pixel cell size and set canvas internal size to exact multiples
    CELL = Math.max(1, Math.floor(cellCss * dpr));
    CAN.width = CELL * BW;
    CAN.height = CELL * BH;
    // Reflect internal size back to CSS pixels to preserve crispness
    CAN.style.width = (CAN.width / dpr) + 'px';
    CAN.style.height = (CAN.height / dpr) + 'px';

    W = CAN.width;
    H = CAN.height;

    // Reset any transforms and set crisp rendering
    CTX2.setTransform(1, 0, 0, 1, 0, 0);
    CTX2.imageSmoothingEnabled = false;

    // Size the next preview canvas proportionally so it remains legible
    const next = document.getElementById('next1');
    if (next) {
        const miniCellCss = Math.max(10, Math.min(28, Math.round(cellCss * 1.2)));
        const miniCss = miniCellCss * 4; // 4x4 grid is enough for any tetromino
        next.style.width = miniCss + 'px';
        next.style.height = miniCss + 'px';
        next.width = Math.floor(miniCss * dpr);
        next.height = Math.floor(miniCss * dpr);
        const nctx = next.getContext('2d');
        nctx.setTransform(1, 0, 0, 1, 0, 0);
        nctx.imageSmoothingEnabled = false;
    }
}

resizeBoard();
window.addEventListener('resize', () => {
    const was = game.running && !game.paused;
    game.paused = true;
    resizeBoard();
    draw();
    game.paused = !was;
});

function colorOf(t) {
    const map = {I: '--cI', J: '--cJ', L: '--cL', O: '--cO', S: '--cS', T: '--cT', Z: '--cZ'};
    return map[t] || null;
}

function cssVar(name) {
    if (!name) return '';
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

// === Level-based Theme switching ===
// Expanded theme roster and switch every level (1→first, 2→second, ... cycling)
const THEMES = ['theme-neon', 'theme-ocean', 'theme-sunset', 'theme-contrast', 'theme-forest', 'theme-magma', 'theme-ice', 'theme-candy', 'theme-cyber', 'theme-mono'];
let currentTheme = null;

function applyThemeByLevel(level) {
    const idx = (level - 1) % THEMES.length; // change on every level up
    const name = THEMES[idx];
    if (name !== currentTheme) {
        const root = document.documentElement; // :root carries our CSS vars
        if (currentTheme) root.classList.remove(currentTheme);
        root.classList.add(name);
        currentTheme = name;
    }
}

// ===== Game State =====
const game = {
    running: false,
    paused: false,
    over: false,
    score: 0,
    lines: 0,
    level: 1,
    fallMs: 800,
    accum: 0,
    best: Number(localStorage.getItem(LS_KEY) || 0),
    startedAt: null,
    endedAt: null
};
(document.getElementById('best')).textContent = game.best;

// 7-bag tetrominoes
const SHAPES = {
    I: [[1, 1, 1, 1]],
    J: [[1, 0, 0], [1, 1, 1]],
    L: [[0, 0, 1], [1, 1, 1]],
    O: [[1, 1], [1, 1]],
    S: [[0, 1, 1], [1, 1, 0]],
    T: [[0, 1, 0], [1, 1, 1]],
    Z: [[1, 1, 0], [0, 1, 1]],
};

function rotate(mat) {
    const h = mat.length, w = mat[0].length;
    const out = Array.from({length: w}, () => Array(h).fill(0));
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) out[x][h - 1 - y] = mat[y][x];
    return out;
}

function clone(m) {
    return m.map(r => r.slice());
}

let bag = [];

function nextType() {
    if (bag.length === 0) {
        bag = ['I', 'J', 'L', 'O', 'S', 'T', 'Z'];
        for (let i = bag.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [bag[i], bag[j]] = [bag[j], bag[i]];
        }
    }
    return bag.pop();
}

let grid = []; // BH rows of BW
let cur = null, hold = null, holdUsed = false, nextT = nextType();

function spawn() {
    const type = nextT;
    nextT = nextType();
    cur = {t: type, m: clone(SHAPES[type]), x: Math.floor(BW / 2) - 1, y: -2};
    holdUsed = false;
    if (collides(cur.m, cur.x, cur.y)) {
        gameOver();
        return;
    }
    renderMini(document.getElementById('next1'), nextT);
}

function collides(m, x, y) {
    const h = m.length, w = m[0].length;
    for (let yy = 0; yy < h; yy++) for (let xx = 0; xx < w; xx++) {
        if (!m[yy][xx]) continue;
        const gx = x + xx, gy = y + yy;
        if (gx < 0 || gx >= BW || gy >= BH) {
            if (gy < 0) continue;
            return true;
        }
        if (gy >= 0 && grid[gy][gx]) return true;
    }
    return false;
}

function place() {
    const m = cur.m, h = m.length, w = m[0].length;
    // Game Over, wenn etwas außerhalb landet würde
    for (let yy = 0; yy < h; yy++) for (let xx = 0; xx < w; xx++) {
        if (!m[yy][xx]) continue;
        const gx = cur.x + xx, gy = cur.y + yy;
        if (gx < 0 || gx >= BW || gy < 0 || gy >= BH) {
            gameOver();
            return;
        }
    }
    // Platzieren
    for (let yy = 0; yy < h; yy++) for (let xx = 0; xx < w; xx++) {
        if (m[yy][xx]) {
            const gx = cur.x + xx, gy = cur.y + yy;
            grid[gy][gx] = cur.t;
        }
    }
    // Linien prüfen
    let cleared = 0;
    for (let y = BH - 1; y >= 0; y--) {
        if (grid[y].every(v => v)) {
            grid.splice(y, 1);
            grid.unshift(Array(BW).fill(null));
            cleared++;
            y++;
        }
    }
    if (cleared) {
        game.lines += cleared;
        const points = [0, 100, 300, 500, 800][cleared] || cleared * 200;
        game.score += points * game.level;
        updateHUD();
        updateLevel();
    }
    spawn();
}

function updateLevel() {
    game.level = 1 + Math.floor(game.lines / 8);
    game.fallMs = Math.max(80, 600 - (game.level - 1) * 60);
    document.getElementById('speed').textContent = `Fallrate: ${game.fallMs}ms`;
    document.getElementById('level').textContent = game.level;
    // apply level-based theme
    applyThemeByLevel(game.level);
}

function hardDrop() {
    while (!collides(cur.m, cur.x, cur.y + 1)) cur.y++;
    place();
}

function softDrop() {
    if (!collides(cur.m, cur.x, cur.y + 1)) cur.y++; else place();
}

function move(dx) {
    if (!collides(cur.m, cur.x + dx, cur.y)) cur.x += dx;
}

function rotateCur() {
    const r = rotate(cur.m);
    if (!collides(r, cur.x, cur.y)) cur.m = r;
    else if (!collides(r, cur.x - 1, cur.y)) {
        cur.x -= 1;
        cur.m = r;
    } else if (!collides(r, cur.x + 1, cur.y)) {
        cur.x += 1;
        cur.m = r;
    }
}

// Hold ist deaktiviert – no-op, damit alte Tastenbelegungen keinen Effekt haben
function holdSwap() {
    return; // intentionally disabled
}

function reset() {
    grid = Array.from({length: BH}, () => Array(BW).fill(null));
    bag = [];
    nextT = nextType();
    cur = null;
    hold = null;
    holdUsed = false;
    game.score = 0;
    game.lines = 0;
    game.level = 1;
    game.fallMs = 800;
    game.accum = 0;
    updateHUD();
    updateLevel();
    renderMini(document.getElementById('hold'), null);
    renderMini(document.getElementById('next1'), nextT);
}

function updateHUD() {
    document.getElementById('score').textContent = game.score;
    document.getElementById('lines').textContent = game.lines;
    document.getElementById('best').textContent = game.best;
}

// ===== Rendering =====
function glowBlurPx() {
    const base = parseFloat(cssVar('--glowStrength')) || 0;
    return Math.max(0, Math.round(base * (CELL / 24)));
}

function drawCell(px, py, size, color, mode = 'normal') {
    CTX2.save();
    if (mode === 'ghost') {
        CTX2.globalAlpha = 0.35;
        // no glow for ghost to keep it subtle
    } else {
        const glowColor = cssVar('--glow') || color;
        const blur = glowBlurPx();
        if (blur > 0) {
            CTX2.shadowColor = glowColor;
            CTX2.shadowBlur = blur;
            CTX2.shadowOffsetX = 0;
            CTX2.shadowOffsetY = 0;
        }
    }
    CTX2.fillStyle = color;
    // draw with slight inset to show grid lines
    CTX2.fillRect(px + 1, py + 1, size - 2, size - 2);
    CTX2.restore();
}

function draw() {
    // bg
    CTX2.fillStyle = cssVar('--board');
    CTX2.fillRect(0, 0, W, H);
    // grid lines subtle
    CTX2.strokeStyle = cssVar('--grid');
    CTX2.lineWidth = 1;
    CTX2.beginPath();
    for (let x = 0; x <= BW; x++) {
        CTX2.moveTo(x * CELL, 0);
        CTX2.lineTo(x * CELL, BH * CELL);
    }
    for (let y = 0; y <= BH; y++) {
        CTX2.moveTo(0, y * CELL);
        CTX2.lineTo(BW * CELL, y * CELL);
    }
    CTX2.stroke();
    // fixed blocks
    for (let y = 0; y < BH; y++) for (let x = 0; x < BW; x++) {
        const t = grid[y][x];
        if (!t) continue;
        const color = cssVar(colorOf(t)) || '#888';
        drawCell(x * CELL, y * CELL, CELL, color, 'fixed');
    }
    if (cur) {
        // ghost
        let gy = cur.y;
        while (!collides(cur.m, cur.x, gy + 1)) gy++;
        const ghostColor = cssVar('--ghost') || 'rgba(255,255,255,0.25)';
        drawMatrix(cur.m, cur.x, gy, false, 'ghost', ghostColor);
        // current piece
        const curColor = cssVar(colorOf(cur.t)) || '#888';
        drawMatrix(cur.m, cur.x, cur.y, true, 'current', curColor);
    }
}

function drawMatrix(m, ox, oy, rounded = false, mode = 'normal', colorOverride = null) {
    const h = m.length, w = m[0].length;
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
        if (!m[y][x]) continue;
        const px = (ox + x) * CELL, py = (oy + y) * CELL;
        const color = colorOverride || '#888';
        drawCell(px, py, CELL, color, mode);
    }
}

function renderMini(c, type) {
    if (!c) return; // tolerate missing canvas (e.g., hold removed)
    const ctx = c.getContext('2d');
    ctx.clearRect(0, 0, c.width, c.height);
    if (!type) return;
    const m = SHAPES[type];
    const cell = 24;
    const w = m[0].length, h = m.length;
    const ox = Math.floor((c.width - w * cell) / 2), oy = Math.floor((c.height - h * cell) / 2);
    ctx.fillStyle = cssVar(colorOf(type)) || '#888';
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
        if (m[y][x]) ctx.fillRect(ox + x * cell + 2, oy + y * cell + 2, cell - 4, cell - 4);
    }
}

// ===== Loop =====
let last = 0, rafId;

function loop(ts) {
    if (!last) last = ts;
    const dt = ts - last;
    last = ts;
    if (game.running && !game.paused && !game.over) {
        // Level zeitbasiert nachführen
        updateLevel();
        game.accum += dt;
        if (game.accum >= game.fallMs) {
            game.accum = 0;
            if (!collides(cur.m, cur.x, cur.y + 1)) cur.y++; else place();
        }
        draw();
    }
    rafId = requestAnimationFrame(loop);
}

// ===== Start / Over =====
function start() {
    reset();
    spawn();
    game.running = true;
    game.over = false;
    game.startedAt = new Date();
    document.body.classList.add('playing');
    document.getElementById('startLayer').classList.add('hidden');
    document.getElementById('gameOverLayer').classList.add('hidden');
    cancelAnimationFrame(rafId);
    last = 0;
    rafId = requestAnimationFrame(loop);
}

function gameOver() {
    if (game.over) return;
    game.over = true;
    game.running = false;
    document.body.classList.remove('playing');
    game.endedAt = new Date();
    if (game.score > game.best) {
        game.best = game.score;
        localStorage.setItem(LS_KEY, String(game.best));
    }
    updateHUD();
    showSummaryAndSubmit();
}

// ===== Controls =====
const keymap = {
    ArrowLeft: 'L',
    ArrowRight: 'R',
    ArrowDown: 'D',
    ArrowUp: 'ROT',
    Space: 'HARD',
    KeyA: 'L',
    KeyD: 'R',
    KeyS: 'D',
    KeyW: 'ROT',
    KeyX: 'ROT',
    KeyZ: 'ROT_CCW',
    KeyP: 'PAUSE'
};

function handleGameKeys(e) {
    const act = keymap[e.code];
    if (!act) return false;
    e.preventDefault();
    if (!game.running) return false;
    switch (act) {
        case 'L':
            move(-1);
            break;
        case 'R':
            move(1);
            break;
        case 'D':
            softDrop();
            break;
        case 'ROT':
            rotateCur();
            break;
        case 'ROT_CCW':
            rotateCur();
            rotateCur();
            rotateCur();
            break;
        case 'HARD':
            hardDrop();
            break;
        case 'HOLD':
            holdSwap();
            break;
        case 'PAUSE':
            game.paused = !game.paused;
            break;
    }
    draw();
    return true;
}

document.addEventListener('keydown', (e) => {
    if (handleGameKeys(e)) return;
    // allow Enter/Space to start when not running
    const startLayer = document.getElementById('startLayer');
    if (!game.running && !startLayer.classList.contains('hidden')) {
        if (e.code === 'Enter' || e.code === 'Space') {
            e.preventDefault();
            start();
        }
    }
});

// Touch (mobile): single-gesture controls
// - Swipe left/right: move by 1
// - Swipe down: HARD DROP
// - Tap: rotate clockwise
// - Holding does nothing
let t0 = null;
const TAP_MAX_MS = 250;
const TAP_MAX_MOVE = 12; // px
const SWIPE_MIN_DIST = 24; // px

CAN.addEventListener('touchstart', e => {
    if (!game.running) return;
    const t = e.touches[0];
    t0 = {x: t.clientX, y: t.clientY, time: performance.now()};
    e.preventDefault();
}, {passive: false});

// We intentionally do not act on touchmove to avoid repeats while holding
CAN.addEventListener('touchmove', e => {
    if (!game.running) return;
    // Prevent page scroll/overscroll while interacting
    e.preventDefault();
}, {passive: false});

CAN.addEventListener('touchend', e => {
    if (!t0) return;
    const t1 = {time: performance.now()};
    const changed = e.changedTouches?.[0];
    const dx = (changed?.clientX ?? 0) - t0.x;
    const dy = (changed?.clientY ?? 0) - t0.y;
    const adx = Math.abs(dx), ady = Math.abs(dy);
    const dt = t1.time - t0.time;

    if (adx <= TAP_MAX_MOVE && ady <= TAP_MAX_MOVE && dt <= TAP_MAX_MS) {
        // Tap → rotate
        if (game.running) {
            rotateCur();
            draw();
        }
    } else if (ady > adx && dy > SWIPE_MIN_DIST) {
        // Down swipe → hard drop
        hardDrop();
        draw();
    } else if (adx >= SWIPE_MIN_DIST) {
        // Horizontal swipe → move one step
        move(dx > 0 ? 1 : -1);
        draw();
    }

    t0 = null;
    e.preventDefault();
}, {passive: false});

// Buttons
const startBtn = document.getElementById('startBtn');
const againBtn = document.getElementById('againBtn');

function safeStart(ev) {
    ev?.preventDefault?.();
    ev?.stopPropagation?.();
    if (game.running) return;
    start();
}

startBtn.setAttribute('type', 'button');
startBtn.style.touchAction = 'manipulation';
startBtn.addEventListener('click', safeStart);
startBtn.addEventListener('pointerdown', safeStart, {passive: false});
startBtn.addEventListener('touchend', safeStart, {passive: false});
// Start by tapping/clicking anywhere on the start layer
const startLayer = document.getElementById('startLayer');
startLayer.addEventListener('pointerdown', (e) => {
    if (e.target.id !== "startBtn") {
        e.preventDefault();
        safeStart(e);
    }
}, {passive: false});
againBtn.addEventListener('click', (e) => {
    e.preventDefault();
    start();
});

// Pause visibility
document.addEventListener('visibilitychange', () => {
    if (document.hidden && CFG.requireVisibility) {
        game.paused = true;
    } else if (game.running) {
        game.paused = false;
    }
});

// ===== Submit Result =====
async function postResult(url, token, payload) {
    if (!url) return {status: 'ok', local: true};
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
    const s = document.getElementById('summary');
    s.innerHTML = `Score: <b>${game.score}</b><br>Lines: ${game.lines}<br>Level: ${game.level}`;
    const submitState = document.getElementById('submitState');
    const errBox = document.getElementById('errBox');
    errBox.classList.add('hidden');
    errBox.textContent = '';
    layer.classList.remove('hidden');
    const payload = {
        partyId: CTX.partyId,
        playerId: CTX.playerId,
        gameId: CTX.gameId,
        sessionId: CTX.sessionId,
        score: game.score,
        maxScore: 999999,
        completed: true,
        durationMs: game.endedAt - game.startedAt,
        startedAt: game.startedAt.toISOString(),
        endedAt: game.endedAt.toISOString(),
        attempt: 1,
        metadata: {lines: game.lines, level: game.level},
        clientInfo: {userAgent: navigator.userAgent, viewport: {w: innerWidth, h: innerHeight}}
    };
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
                btn.onclick = () => open(res.scoreboardUrl, '_blank');
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

// ===== Init =====
(function init() {
    document.getElementById('ctx').textContent = [`party:${CTX.partyId?.slice(0, 8) || '—'}`, `player:${CTX.playerId?.slice(0, 8) || '—'}`, `game:${CTX.gameId}`].join(' • ');
    reset();
    draw();
    document.getElementById('startLayer').classList.remove('hidden');
})();
