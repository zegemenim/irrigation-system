<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Istabreeze 500W — Rüzgar Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        @theme {
            --color-teal-350: #4fd1c5;
        }
    </style>
    <style>
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        /* bg mesh */
        body {
            background-color: #03060f;
            background-image:
                radial-gradient(ellipse 80% 55% at 0% 0%, rgba(13,148,136,0.22) 0%, transparent 70%),
                radial-gradient(ellipse 55% 45% at 100% 100%, rgba(29,78,216,0.14) 0%, transparent 70%);
        }

        /* glass card */
        .glass {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(16px);
        }

        /* preset buttons */
        .pb {
            padding: 6px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            font-size: 12px;
            font-weight: 600;
            color: rgb(148,163,184);
            cursor: pointer;
            transition: all 0.15s ease;
            letter-spacing: 0.01em;
        }
        .pb:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
        .pb.pb-on {
            border-color: rgba(45,212,191,0.55);
            background: rgba(45,212,191,0.12);
            color: rgb(94,234,212);
        }
        .pb.pb-custom-on {
            border-color: rgba(251,191,36,0.55);
            background: rgba(251,191,36,0.1);
            color: rgb(253,224,71);
        }

        /* custom panel */
        #custom-panel {
            display: none;
            animation: sDown 0.18s ease;
        }
        #custom-panel.open { display: flex; }
        @keyframes sDown {
            from { opacity:0; transform: translateY(-8px); }
            to   { opacity:1; transform: translateY(0); }
        }

        input[type="datetime-local"] {
            color-scheme: dark;
            font-family: inherit;
        }

        /* stat bar */
        .sbar { transition: width 0.7s cubic-bezier(0.4,0,0.2,1); }

        /* live dot */
        @keyframes livepulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(52,211,153,0.5); }
            50%      { box-shadow: 0 0 0 5px rgba(52,211,153,0); }
        }
        .live-dot { animation: livepulse 2s ease-in-out infinite; }

        /* chart wrapper — full height on desktop */
        .chart-wrap { height: 340px; }
        @media (min-width: 1024px) { .chart-wrap { height: 420px; } }
        @media (min-width: 1280px) { .chart-wrap { height: 480px; } }
    </style>
</head>
<body class="min-h-screen text-white antialiased">

<div class="mx-auto max-w-[1400px] space-y-6 px-4 py-7 sm:px-6 xl:px-10">

    {{-- ════════════════════════════════════════════════════════════ HEADER --}}
    <header class="flex flex-col gap-4 border-b border-white/[0.07] pb-6 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-teal-400">Canlı Rüzgar İzleme</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Istabreeze&nbsp;<span class="text-teal-300">500W</span>
            </h1>
        </div>

        <div class="flex items-center gap-2.5 self-start rounded-xl glass px-4 py-2.5 text-sm md:self-auto">
            <span id="live-dot" class="live-dot h-2.5 w-2.5 rounded-full bg-amber-400"></span>
            <span id="last-updated" class="text-slate-300">Veri bekleniyor…</span>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════════ STAT CARDS --}}
    <section class="grid gap-4 sm:grid-cols-2">

        {{-- Rüzgar hızı --}}
        <article class="glass relative overflow-hidden rounded-2xl p-6 shadow-xl">
            <div class="pointer-events-none absolute inset-0"
                 style="background:radial-gradient(circle at 100% 0%, rgba(45,212,191,0.09) 0%, transparent 60%)"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Anlık Rüzgar Hızı</p>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span id="current-wind-speed"
                          class="text-[64px] font-extrabold leading-none tabular-nums tracking-tight text-white">—</span>
                    <span class="text-2xl font-medium text-teal-300">m/s</span>
                </div>
                <div class="mt-5 space-y-1.5">
                    <div class="flex justify-between text-[11px] font-medium text-slate-500">
                        <span>0 m/s (durgun)</span><span>12 m/s (tam güç)</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full" style="background:rgba(255,255,255,0.07)">
                        <div id="wind-speed-bar"
                             class="sbar h-full rounded-full"
                             style="width:0%;background:linear-gradient(90deg,#2dd4bf,#67e8f9)"></div>
                    </div>
                </div>
            </div>
        </article>

        {{-- Güç --}}
        <article class="glass relative overflow-hidden rounded-2xl p-6 shadow-xl">
            <div class="pointer-events-none absolute inset-0"
                 style="background:radial-gradient(circle at 100% 0%, rgba(251,191,36,0.08) 0%, transparent 60%)"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Anlık Üretilen Güç</p>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span id="current-generated-power"
                          class="text-[64px] font-extrabold leading-none tabular-nums tracking-tight text-white">—</span>
                    <span class="text-2xl font-medium text-amber-300">W</span>
                </div>
                <div class="mt-5 space-y-1.5">
                    <div class="flex justify-between text-[11px] font-medium text-slate-500">
                        <span>0 W (üretim yok)</span><span>500 W (maksimum)</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full" style="background:rgba(255,255,255,0.07)">
                        <div id="generated-power-bar"
                             class="sbar h-full rounded-full"
                             style="width:0%;background:linear-gradient(90deg,#34d399,#fbbf24,#f97316)"></div>
                    </div>
                </div>
            </div>
        </article>

    </section>

    {{-- ══════════════════════════════════════════════════════ TIME TOOLBAR --}}
    <section class="flex flex-col gap-3">

        {{-- Preset + custom toggle row --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Dönem</span>

            <div class="flex flex-wrap gap-1.5" id="preset-wrap">
                <button class="pb pb-on" data-min="0">Tümü</button>
                <button class="pb" data-min="15">15 dk</button>
                <button class="pb" data-min="30">30 dk</button>
                <button class="pb" data-min="60">1 sa</button>
                <button class="pb" data-min="360">6 sa</button>
                <button class="pb" data-min="1440">24 sa</button>
                <button class="pb" data-min="10080">7 gün</button>
            </div>

            <div class="mx-2 h-4 w-px bg-white/10"></div>

            <button id="btn-custom" class="pb inline-flex items-center gap-1.5">
                {{-- Calendar icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Özel aralık
            </button>
        </div>

        {{-- Custom date-range panel --}}
        <div id="custom-panel" class="flex-wrap items-end gap-3 glass rounded-xl px-5 py-4">
            <div class="flex flex-col gap-1">
                <label for="dt-from" class="text-[11px] font-medium text-slate-400">Başlangıç</label>
                <input id="dt-from" type="datetime-local"
                       class="rounded-lg border border-white/10 bg-slate-800 px-3 py-2 text-sm text-white
                              focus:border-teal-400/50 focus:outline-none focus:ring-2 focus:ring-teal-400/20">
            </div>
            <div class="flex flex-col gap-1">
                <label for="dt-to" class="text-[11px] font-medium text-slate-400">Bitiş</label>
                <input id="dt-to" type="datetime-local"
                       class="rounded-lg border border-white/10 bg-slate-800 px-3 py-2 text-sm text-white
                              focus:border-teal-400/50 focus:outline-none focus:ring-2 focus:ring-teal-400/20">
            </div>
            <div class="flex items-end gap-2">
                <button id="btn-apply"
                        class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white shadow-md transition active:scale-95"
                        style="background:linear-gradient(135deg,#0d9488,#2563eb)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Uygula
                </button>
                <button id="btn-clear"
                        class="rounded-lg border border-white/10 bg-white/[0.05] px-4 py-2 text-sm font-medium text-slate-400 transition hover:bg-white/[0.1]">
                    Sıfırla
                </button>
            </div>
            <p id="custom-err" class="hidden w-full text-xs text-red-400 pt-1"></p>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════ CHARTS --}}

    {{-- Wind speed chart --}}
    <section class="glass rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between border-b border-white/[0.07] px-6 py-5">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-teal-400">Rüzgar Hızı Değişimi</p>
                <h2 class="mt-1 text-xl font-bold text-white">Zaman — m/s</h2>
            </div>
            <div class="flex items-center gap-2 rounded-xl px-3.5 py-2" style="background:rgba(45,212,191,0.1)">
                <span class="h-2.5 w-2.5 rounded-full" style="background:#2dd4bf"></span>
                <span class="text-sm font-bold text-teal-300">m/s</span>
            </div>
        </div>
        <div class="chart-wrap p-4 pt-2">
            <canvas id="chart-wind"></canvas>
        </div>
    </section>

    {{-- Power chart --}}
    <section class="glass rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between border-b border-white/[0.07] px-6 py-5">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-amber-400">Elektrik Üretimi</p>
                <h2 class="mt-1 text-xl font-bold text-white">Zaman — Watt</h2>
            </div>
            <div class="flex items-center gap-2 rounded-xl px-3.5 py-2" style="background:rgba(251,191,36,0.1)">
                <span class="h-2.5 w-2.5 rounded-full" style="background:#fbbf24"></span>
                <span class="text-sm font-bold text-amber-300">W</span>
            </div>
        </div>
        <div class="chart-wrap p-4 pt-2">
            <canvas id="chart-power"></canvas>
        </div>
    </section>

    <p class="pb-4 text-center text-[11px] text-slate-600">Veriler her 5 saniyede bir otomatik yenilenir.</p>
</div>

<script>
// ─── Config ────────────────────────────────────────────────────────────────
const API_URL = @json(route('api.wind.chart-data'));

const C_TEAL      = '#2dd4bf';
const C_AMBER     = '#fbbf24';
const C_GRID      = 'rgba(148,163,184,0.1)';
const C_TICK      = 'rgba(148,163,184,0.75)';
const C_BG        = '#03060f';

Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.size   = 12.5;
Chart.defaults.color       = C_TICK;

// ─── State ─────────────────────────────────────────────────────────────────
let selMinutes = 0;       // 0 = all
let cfrom = null, cto = null, customMode = false;
let panelOpen = false;

// ─── Gradient helper ────────────────────────────────────────────────────────
function grad(ctx, top, bot) {
    const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 420);
    g.addColorStop(0,   top);
    g.addColorStop(1,   bot);
    return g;
}

// ─── Base chart options ─────────────────────────────────────────────────────
function opts(sugMax, unit) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600, easing: 'easeInOutQuart' },
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor : 'rgba(2,6,23,0.95)',
                borderColor     : 'rgba(255,255,255,0.1)',
                borderWidth     : 1,
                padding         : { x: 16, y: 12 },
                cornerRadius    : 12,
                titleColor      : 'rgba(148,163,184,0.85)',
                titleFont       : { size: 12 },
                bodyColor       : '#fff',
                bodyFont        : { weight: '700', size: 15 },
                callbacks: {
                    label: ctx => `  ${ctx.parsed.y} ${unit}`,
                },
            },
        },
        scales: {
            x: {
                grid  : { color: C_GRID },
                border: { display: false },
                ticks : {
                    color        : C_TICK,
                    maxRotation  : 0,
                    autoSkip     : true,
                    maxTicksLimit: 12,
                    padding      : 8,
                    font         : { size: 12 },
                },
            },
            y: {
                beginAtZero  : true,
                suggestedMax : sugMax,
                grid         : { color: C_GRID },
                border       : { display: false },
                ticks        : {
                    color  : C_TICK,
                    padding: 12,
                    font   : { size: 12 },
                    callback: v => `${v} ${unit}`,
                },
            },
        },
    };
}

// ─── Build chart ────────────────────────────────────────────────────────────
function mkChart(id, line, gradTop, gradBot, sugMax, unit) {
    const canvas = document.getElementById(id);
    const ctx    = canvas.getContext('2d');

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels  : [],
            datasets: [{
                label                  : unit,
                data                   : [],
                borderColor            : line,
                backgroundColor        : () => grad(ctx, gradTop, gradBot),
                borderWidth            : 2.5,
                pointRadius            : 0,
                pointHitRadius         : 20,
                pointHoverRadius       : 6,
                pointHoverBackgroundColor : line,
                pointHoverBorderColor  : '#020617',
                pointHoverBorderWidth  : 2.5,
                fill                   : true,
                tension                : 0.42,
            }],
        },
        options: opts(sugMax, unit),
    });
}

const windChart  = mkChart('chart-wind',  C_TEAL,  'rgba(45,212,191,0.28)',  'rgba(45,212,191,0.0)',  14,  'm/s');
const powerChart = mkChart('chart-power', C_AMBER, 'rgba(251,191,36,0.22)',  'rgba(251,191,36,0.0)',  500, 'W');

// ─── Update helpers ─────────────────────────────────────────────────────────
function updChart(chart, labels, vals) {
    chart.data.labels            = labels;
    chart.data.datasets[0].data = vals;
    chart.update('active');
}

function updStats(r) {
    const ws = r ? Number(r.wind_speed)      : null;
    const gp = r ? Number(r.generated_power) : null;

    document.getElementById('current-wind-speed').textContent      = ws !== null ? ws.toFixed(2) : '—';
    document.getElementById('current-generated-power').textContent = gp !== null ? gp.toFixed(2) : '—';
    document.getElementById('wind-speed-bar').style.width      = ws !== null ? `${Math.min((ws/12)*100,100)}%`   : '0%';
    document.getElementById('generated-power-bar').style.width = gp !== null ? `${Math.min((gp/500)*100,100)}%` : '0%';
}

function setConn(ok, label) {
    const dot  = document.getElementById('live-dot');
    const text = document.getElementById('last-updated');
    dot.style.background  = ok ? '#34d399' : '#f87171';
    dot.style.boxShadow   = ok ? '0 0 0 0 rgba(52,211,153,0.5)' : '0 0 0 0 rgba(248,113,113,0.5)';
    text.textContent = label;
}

// ─── Build request URL ──────────────────────────────────────────────────────
function buildUrl() {
    const p = new URLSearchParams();
    if (customMode && cfrom && cto) {
        p.set('from', cfrom);
        p.set('to',   cto);
    } else if (selMinutes > 0) {
        p.set('minutes', selMinutes);
    }
    // selMinutes === 0 && !customMode → no params → all records
    const qs = p.toString();
    return qs ? `${API_URL}?${qs}` : API_URL;
}

// ─── Fetch & refresh ────────────────────────────────────────────────────────
async function refresh() {
    try {
        const res = await fetch(buildUrl(), { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);

        const { data: rows } = await res.json();
        if (!rows) return;

        const labels = rows.map(r => r.timestamp);
        updChart(windChart,  labels, rows.map(r => Number(r.wind_speed)));
        updChart(powerChart, labels, rows.map(r => Number(r.generated_power)));
        updStats(rows.at(-1));

        const last = rows.at(-1);
        setConn(true, last ? `Son okuma: ${last.timestamp}  ·  ${rows.length} kayıt` : 'Kayıt yok');
    } catch {
        setConn(false, 'Bağlantı hatası');
    }
}

// ─── Preset button logic ────────────────────────────────────────────────────
function activatePreset(min) {
    selMinutes = min;
    customMode = false;
    document.querySelectorAll('#preset-wrap .pb').forEach(b => {
        const isThis = parseInt(b.dataset.min, 10) === min;
        b.classList.toggle('pb-on', isThis);
    });
    document.getElementById('btn-custom').classList.remove('pb-custom-on');
    closePanel();
    refresh();
}

document.getElementById('preset-wrap').addEventListener('click', e => {
    const b = e.target.closest('.pb[data-min]');
    if (b) { activatePreset(parseInt(b.dataset.min, 10)); }
});

// ─── Custom panel toggle ────────────────────────────────────────────────────
function closePanel() {
    panelOpen = false;
    document.getElementById('custom-panel').classList.remove('open');
}

document.getElementById('btn-custom').addEventListener('click', () => {
    panelOpen = !panelOpen;
    document.getElementById('custom-panel').classList.toggle('open', panelOpen);
    const btn = document.getElementById('btn-custom');
    btn.classList.toggle('pb-custom-on', panelOpen);

    if (panelOpen && !document.getElementById('dt-from').value) {
        const now = new Date();
        const h24 = new Date(+now - 86400000);
        document.getElementById('dt-from').value = fmtLocal(h24);
        document.getElementById('dt-to').value   = fmtLocal(now);
    }
});

// ─── Apply custom range ─────────────────────────────────────────────────────
document.getElementById('btn-apply').addEventListener('click', () => {
    const fv = document.getElementById('dt-from').value;
    const tv = document.getElementById('dt-to').value;
    const err = document.getElementById('custom-err');

    if (!fv || !tv) {
        err.textContent = 'Lütfen başlangıç ve bitiş zamanı seçin.';
        err.classList.remove('hidden');
        return;
    }
    if (new Date(fv) >= new Date(tv)) {
        err.textContent = 'Başlangıç zamanı bitişten önce olmalıdır.';
        err.classList.remove('hidden');
        return;
    }

    err.classList.add('hidden');
    cfrom      = new Date(fv).toISOString();
    cto        = new Date(tv).toISOString();
    customMode = true;

    // Deactivate presets visually
    document.querySelectorAll('#preset-wrap .pb').forEach(b => b.classList.remove('pb-on'));
    refresh();
});

// ─── Clear custom ────────────────────────────────────────────────────────────
document.getElementById('btn-clear').addEventListener('click', () => {
    document.getElementById('dt-from').value = '';
    document.getElementById('dt-to').value   = '';
    document.getElementById('custom-err').classList.add('hidden');
    cfrom = cto = null;
    customMode = false;
    activatePreset(0); // back to "Tümü"
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
function fmtLocal(d) {
    const p = n => String(n).padStart(2,'0');
    return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}

// ─── Boot ─────────────────────────────────────────────────────────────────────
refresh();
setInterval(refresh, 5000);
</script>
</body>
</html>
