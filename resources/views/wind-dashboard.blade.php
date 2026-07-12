<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Istabreeze 500W — Rüzgar Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        /* ── Custom date-picker panel ── */
        #custom-panel {
            display: none;
            animation: fadeSlideIn 0.18s ease;
        }
        #custom-panel.open { display: flex; }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Datetime input styling ── */
        input[type="datetime-local"] {
            color-scheme: dark;
        }

        /* ── Animated stat bars ── */
        .stat-bar-fill {
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── Pulsing dot ── */
        @keyframes ping-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(1.6); }
        }
        .ping-slow { animation: ping-slow 2s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
<main class="min-h-screen bg-[radial-gradient(ellipse_80%_50%_at_0%_0%,rgba(15,118,110,0.35),transparent),radial-gradient(ellipse_60%_40%_at_100%_100%,rgba(30,58,138,0.25),transparent),#020617]">
    <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">

        {{-- ── Header ── --}}
        <header class="flex flex-col gap-4 border-b border-white/10 pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-400">Canlı Rüzgar İzleme</p>
                <h1 class="mt-1.5 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Istabreeze <span class="text-teal-300">500W</span></h1>
            </div>
            <div class="flex items-center gap-2.5 rounded-xl border border-white/10 bg-white/[0.06] px-4 py-2.5 text-sm text-slate-300 backdrop-blur">
                <span class="relative flex h-2.5 w-2.5">
                    <span id="connection-ping" class="ping-slow absolute inline-flex h-full w-full rounded-full bg-amber-300 opacity-75"></span>
                    <span id="connection-indicator" class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                </span>
                <span id="last-updated">Veri bekleniyor…</span>
            </div>
        </header>

        {{-- ── Stat cards ── --}}
        <section class="grid gap-4 sm:grid-cols-2">
            {{-- Wind speed --}}
            <article class="relative overflow-hidden rounded-2xl border border-teal-400/20 bg-white/[0.06] p-6 shadow-xl backdrop-blur">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(45,212,191,0.08),transparent_60%)]"></div>
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Anlık Rüzgar Hızı</p>
                    <div class="mt-3 flex items-end gap-2">
                        <p id="current-wind-speed" class="text-6xl font-bold tabular-nums tracking-tight text-white">0.00</p>
                        <p class="mb-1.5 text-xl font-medium text-teal-300">m/s</p>
                    </div>
                    <div class="mt-4 space-y-1">
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>0 m/s</span><span>12 m/s</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-800/80">
                            <div id="wind-speed-bar" class="stat-bar-fill h-full w-0 rounded-full bg-gradient-to-r from-teal-400 to-cyan-300"></div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Power --}}
            <article class="relative overflow-hidden rounded-2xl border border-amber-400/20 bg-white/[0.06] p-6 shadow-xl backdrop-blur">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.07),transparent_60%)]"></div>
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Anlık Üretilen Güç</p>
                    <div class="mt-3 flex items-end gap-2">
                        <p id="current-generated-power" class="text-6xl font-bold tabular-nums tracking-tight text-white">0.00</p>
                        <p class="mb-1.5 text-xl font-medium text-amber-300">W</p>
                    </div>
                    <div class="mt-4 space-y-1">
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>0 W</span><span>500 W</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-800/80">
                            <div id="generated-power-bar" class="stat-bar-fill h-full w-0 rounded-full bg-gradient-to-r from-emerald-400 via-amber-300 to-orange-400"></div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        {{-- ── Time-range toolbar ── --}}
        <section class="flex flex-col gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Zaman Aralığı</span>

                {{-- Preset buttons --}}
                <div class="flex flex-wrap gap-1.5" id="preset-buttons">
                    <button data-minutes="15"   class="preset-btn">15 dk</button>
                    <button data-minutes="30"   class="preset-btn">30 dk</button>
                    <button data-minutes="60"   class="preset-btn">1 sa</button>
                    <button data-minutes="360"  class="preset-btn">6 sa</button>
                    <button data-minutes="1440" class="preset-btn">24 sa</button>
                    <button data-minutes="10080" class="preset-btn">7 gün</button>
                    <button data-minutes="0"    class="preset-btn preset-active">Tümü</button>
                </div>

                <div class="mx-1 h-5 w-px bg-white/10"></div>

                {{-- Custom button --}}
                <button id="custom-toggle-btn" class="preset-btn inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Özel
                </button>
            </div>

            {{-- Custom date-range panel --}}
            <div id="custom-panel" class="open:flex flex-wrap items-end gap-3 rounded-xl border border-white/10 bg-white/[0.05] p-4 backdrop-blur">
                <div class="flex flex-col gap-1">
                    <label for="range-from" class="text-[11px] font-medium text-slate-400">Başlangıç</label>
                    <input id="range-from" type="datetime-local"
                        class="rounded-lg border border-white/10 bg-slate-800/80 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-teal-400/60 focus:outline-none focus:ring-1 focus:ring-teal-400/30">
                </div>
                <div class="flex flex-col gap-1">
                    <label for="range-to" class="text-[11px] font-medium text-slate-400">Bitiş</label>
                    <input id="range-to" type="datetime-local"
                        class="rounded-lg border border-white/10 bg-slate-800/80 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-teal-400/60 focus:outline-none focus:ring-1 focus:ring-teal-400/30">
                </div>
                <button id="apply-custom-btn"
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-500/90 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-400 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Uygula
                </button>
                <button id="clear-custom-btn"
                    class="rounded-lg border border-white/10 bg-white/[0.06] px-4 py-2 text-sm font-medium text-slate-400 transition hover:bg-white/[0.1]">
                    Temizle
                </button>
                <p id="custom-error" class="hidden text-xs text-red-400"></p>
            </div>
        </section>

        {{-- ── Charts ── --}}
        <section class="grid flex-1 gap-5 xl:grid-cols-2">

            {{-- Wind speed chart --}}
            <article class="flex flex-col rounded-2xl border border-white/[0.08] bg-slate-900/60 shadow-2xl backdrop-blur">
                <div class="flex items-start justify-between gap-4 p-5 pb-0">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-teal-400">Rüzgar Hızı</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Zaman / m/s</h2>
                    </div>
                    <div class="flex items-center gap-1.5 rounded-lg bg-teal-400/10 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-teal-400"></span>
                        <span class="text-xs font-semibold text-teal-300">m/s</span>
                    </div>
                </div>
                <div class="min-h-[22rem] flex-1 p-4 pt-3">
                    <canvas id="wind-speed-chart"></canvas>
                </div>
            </article>

            {{-- Power chart --}}
            <article class="flex flex-col rounded-2xl border border-white/[0.08] bg-slate-900/60 shadow-2xl backdrop-blur">
                <div class="flex items-start justify-between gap-4 p-5 pb-0">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-amber-400">Elektrik Üretimi</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Zaman / Watt</h2>
                    </div>
                    <div class="flex items-center gap-1.5 rounded-lg bg-amber-400/10 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        <span class="text-xs font-semibold text-amber-300">W</span>
                    </div>
                </div>
                <div class="min-h-[22rem] flex-1 p-4 pt-3">
                    <canvas id="generated-power-chart"></canvas>
                </div>
            </article>
        </section>

    </div>
</main>

{{-- ── Preset button base styles via Tailwind ── --}}
<style>
    .preset-btn {
        padding: 0.35rem 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.05);
        font-size: 0.75rem;
        font-weight: 600;
        color: rgb(148,163,184);
        cursor: pointer;
        transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .preset-btn:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    .preset-btn.preset-active {
        border-color: rgba(45,212,191,0.5);
        background: rgba(45,212,191,0.12);
        color: rgb(94,234,212);
    }
    .custom-active {
        border-color: rgba(251,191,36,0.5) !important;
        background: rgba(251,191,36,0.1) !important;
        color: rgb(253,224,71) !important;
    }
</style>

<script>
    // ── Config ──────────────────────────────────────────────────────────────
    const chartDataUrl = @json(route('api.wind.chart-data'));

    // Colours
    const TEAL    = 'rgb(45,212,191)';
    const AMBER   = 'rgb(251,191,36)';
    const GRID    = 'rgba(148,163,184,0.1)';
    const TICK    = 'rgba(148,163,184,0.75)';

    // State
    let selectedMinutes  = 0;   // 0 = default (last 60)
    let customFrom       = null;
    let customTo         = null;
    let isCustomMode     = false;
    let isPanelOpen      = false;

    // ── Chart.js global defaults ─────────────────────────────────────────────
    Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = TICK;

    // ── Gradient factory (called after canvas is sized) ──────────────────────
    function makeGradient(ctx, color1, color2) {
        const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.offsetHeight || 320);
        g.addColorStop(0,   color1);
        g.addColorStop(1,   color2);
        return g;
    }

    // ── Shared chart options factory ─────────────────────────────────────────
    function chartOptions(suggestedMax, unitLabel) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 500, easing: 'easeInOutQuart' },
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor : 'rgba(2,6,23,0.92)',
                    borderColor     : 'rgba(255,255,255,0.1)',
                    borderWidth     : 1,
                    padding         : 14,
                    cornerRadius    : 10,
                    titleColor      : 'rgba(148,163,184,0.9)',
                    bodyColor       : '#fff',
                    bodyFont        : { weight: '600', size: 13 },
                    callbacks: {
                        label: (ctx) => ` ${ctx.parsed.y} ${unitLabel}`,
                    },
                },
            },
            scales: {
                x: {
                    grid  : { color: GRID, drawBorder: false },
                    border: { display: false },
                    ticks : {
                        color       : TICK,
                        maxRotation : 0,
                        autoSkip    : true,
                        maxTicksLimit: 10,
                        padding     : 6,
                    },
                },
                y: {
                    beginAtZero  : true,
                    suggestedMax,
                    grid  : { color: GRID, drawBorder: false },
                    border: { display: false, dash: [4, 4] },
                    ticks : {
                        color  : TICK,
                        padding: 8,
                        callback: (v) => `${v} ${unitLabel}`,
                    },
                },
            },
        };
    }

    // ── Create charts ─────────────────────────────────────────────────────────
    function buildChart(canvasId, lineColor, gradTop, gradBottom, suggestedMax, unitLabel) {
        const canvas = document.getElementById(canvasId);
        const ctx    = canvas.getContext('2d');

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels  : [],
                datasets: [{
                    label          : unitLabel,
                    data           : [],
                    borderColor    : lineColor,
                    backgroundColor: () => makeGradient(ctx, gradTop, gradBottom),
                    borderWidth    : 2.5,
                    pointRadius    : 0,
                    pointHitRadius : 16,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: lineColor,
                    pointHoverBorderColor    : '#020617',
                    pointHoverBorderWidth    : 2,
                    fill           : true,
                    tension        : 0.4,
                }],
            },
            options: chartOptions(suggestedMax, unitLabel),
        });
    }

    const windSpeedChart = buildChart(
        'wind-speed-chart',
        TEAL,
        'rgba(45,212,191,0.22)',
        'rgba(45,212,191,0.0)',
        14, 'm/s'
    );

    const powerChart = buildChart(
        'generated-power-chart',
        AMBER,
        'rgba(251,191,36,0.18)',
        'rgba(251,191,36,0.0)',
        500, 'W'
    );

    // ── Stat cards ───────────────────────────────────────────────────────────
    function updateStats(reading) {
        const ws = reading ? Number(reading.wind_speed)      : 0;
        const gp = reading ? Number(reading.generated_power) : 0;

        document.getElementById('current-wind-speed').textContent      = ws.toFixed(2);
        document.getElementById('current-generated-power').textContent = gp.toFixed(2);
        document.getElementById('wind-speed-bar').style.width          = `${Math.min((ws / 12) * 100, 100)}%`;
        document.getElementById('generated-power-bar').style.width     = `${Math.min((gp / 500) * 100, 100)}%`;
    }

    // ── Connection indicator ─────────────────────────────────────────────────
    function setConnection(ok, label) {
        const dot  = document.getElementById('connection-indicator');
        const ping = document.getElementById('connection-ping');
        const text = document.getElementById('last-updated');
        const cls  = ok ? 'bg-emerald-400' : 'bg-red-400';
        dot.className  = `relative inline-flex h-2.5 w-2.5 rounded-full ${cls}`;
        ping.className = `ping-slow absolute inline-flex h-full w-full rounded-full ${cls} opacity-75`;
        text.textContent = label;
    }

    // ── Build API URL ────────────────────────────────────────────────────────
    function buildUrl() {
        const params = new URLSearchParams();
        if (isCustomMode && customFrom && customTo) {
            params.set('from', customFrom);
            params.set('to',   customTo);
        } else if (selectedMinutes > 0) {
            params.set('minutes', selectedMinutes);
        }
        const qs = params.toString();
        return qs ? `${chartDataUrl}?${qs}` : chartDataUrl;
    }

    // ── Chart update ─────────────────────────────────────────────────────────
    function updateChart(chart, labels, values) {
        chart.data.labels              = labels;
        chart.data.datasets[0].data   = values;
        chart.update('active');
    }

    // ── Fetch & refresh ───────────────────────────────────────────────────────
    async function refreshDashboard() {
        try {
            const res = await fetch(buildUrl(), { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const { data: readings } = await res.json();
            if (!readings) return;

            const labels = readings.map(r => r.timestamp);
            updateChart(windSpeedChart, labels, readings.map(r => Number(r.wind_speed)));
            updateChart(powerChart,     labels, readings.map(r => Number(r.generated_power)));
            updateStats(readings.at(-1));

            const last = readings.at(-1);
            setConnection(true, last ? `Son okuma: ${last.timestamp}` : 'Kayıt yok');
        } catch (e) {
            setConnection(false, 'Bağlantı hatası');
        }
    }

    // ── Preset button handling ────────────────────────────────────────────────
    const presetButtons = document.getElementById('preset-buttons');
    const customToggle  = document.getElementById('custom-toggle-btn');
    const customPanel   = document.getElementById('custom-panel');

    function activatePreset(btn) {
        document.querySelectorAll('.preset-btn').forEach(b => {
            b.classList.remove('preset-active', 'custom-active');
        });
        btn.classList.add('preset-active');
        isCustomMode    = false;
        selectedMinutes = parseInt(btn.dataset.minutes, 10);
        customPanel.classList.remove('open');
        isPanelOpen = false;
        refreshDashboard();
    }

    presetButtons.addEventListener('click', e => {
        const btn = e.target.closest('.preset-btn');
        if (btn) { activatePreset(btn); }
    });

    // ── Custom toggle ─────────────────────────────────────────────────────────
    customToggle.addEventListener('click', () => {
        isPanelOpen = !isPanelOpen;
        customPanel.classList.toggle('open', isPanelOpen);
        if (isPanelOpen) {
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('preset-active'));
            customToggle.classList.add('custom-active');
            // Default: last 24h
            if (!document.getElementById('range-from').value) {
                const now    = new Date();
                const minus24 = new Date(now - 24 * 60 * 60 * 1000);
                document.getElementById('range-from').value = toLocalDatetimeInput(minus24);
                document.getElementById('range-to').value   = toLocalDatetimeInput(now);
            }
        } else {
            customToggle.classList.remove('custom-active');
        }
    });

    // ── Apply custom range ────────────────────────────────────────────────────
    document.getElementById('apply-custom-btn').addEventListener('click', () => {
        const fromEl = document.getElementById('range-from');
        const toEl   = document.getElementById('range-to');
        const errEl  = document.getElementById('custom-error');

        const fromVal = fromEl.value;
        const toVal   = toEl.value;

        if (!fromVal || !toVal) {
            errEl.textContent = 'Lütfen başlangıç ve bitiş zamanı seçin.';
            errEl.classList.remove('hidden');
            return;
        }
        if (new Date(fromVal) >= new Date(toVal)) {
            errEl.textContent = 'Başlangıç zamanı bitiş zamanından önce olmalıdır.';
            errEl.classList.remove('hidden');
            return;
        }

        errEl.classList.add('hidden');
        customFrom   = new Date(fromVal).toISOString();
        customTo     = new Date(toVal).toISOString();
        isCustomMode = true;
        refreshDashboard();
    });

    // ── Clear custom range ────────────────────────────────────────────────────
    document.getElementById('clear-custom-btn').addEventListener('click', () => {
        document.getElementById('range-from').value = '';
        document.getElementById('range-to').value   = '';
        document.getElementById('custom-error').classList.add('hidden');
        customFrom   = null;
        customTo     = null;
        isCustomMode = false;

        // Reactivate "Tümü"
        const allBtn = document.querySelector('[data-minutes="0"]');
        if (allBtn) { activatePreset(allBtn); }
    });

    // ── Helper: to local datetime-local string ────────────────────────────────
    function toLocalDatetimeInput(date) {
        const pad = n => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    // ── Bootstrap ────────────────────────────────────────────────────────────
    refreshDashboard();
    setInterval(refreshDashboard, 5000);
</script>
</body>
</html>
