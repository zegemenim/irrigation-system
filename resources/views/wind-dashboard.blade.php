<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Istabreeze 500W — Rüzgar Analiz Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        body {
            background-color: #030712;
            background-image:
                radial-gradient(ellipse 90% 60% at 0% -5%,  rgba(13,148,136,0.20) 0%, transparent 65%),
                radial-gradient(ellipse 60% 50% at 100% 105%, rgba(29,78,216,0.12) 0%, transparent 65%);
        }

        /* ── Glass card ──────────────────── */
        .glass {
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .glass-strong {
            background: rgba(255,255,255,0.055);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(24px);
        }

        /* ── Preset buttons ──────────────── */
        .pb {
            padding: 6px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.09);
            background: rgba(255,255,255,0.04);
            font-size: 12px; font-weight: 600;
            color: rgb(148,163,184);
            cursor: pointer; letter-spacing: .01em;
            transition: all .14s ease;
        }
        .pb:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
        .pb.pb-on { border-color: rgba(45,212,191,.5); background: rgba(45,212,191,.12); color: rgb(94,234,212); }
        .pb.pb-co { border-color: rgba(251,191,36,.5); background: rgba(251,191,36,.1); color: rgb(253,224,71); }

        /* ── Custom panel ────────────────── */
        #custom-panel { display:none; animation: sDown .18s ease; }
        #custom-panel.open { display:flex; }
        @keyframes sDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }

        input[type="datetime-local"] { color-scheme:dark; font-family:inherit; }

        /* ── Stat bar ────────────────────── */
        .sbar { transition: width .7s cubic-bezier(.4,0,.2,1); }

        /* ── Live dot ────────────────────── */
        @keyframes livepulse {
            0%,100%{box-shadow:0 0 0 0 rgba(52,211,153,.5)}
            50%{box-shadow:0 0 0 5px rgba(52,211,153,0)}
        }
        .live-dot { animation: livepulse 2s ease-in-out infinite; }

        /* ── Chart heights ───────────────── */
        .chart-wrap        { height: 340px; }
        @media(min-width:1024px){ .chart-wrap{ height: 420px; } }
        @media(min-width:1280px){ .chart-wrap{ height: 480px; } }

        .chart-wrap-hist   { height: 220px; }
        @media(min-width:1024px){ .chart-wrap-hist{ height:260px; } }

        /* ── Verdict badge ───────────────── */
        .verdict-great  { background:rgba(52,211,153,.14);  border-color:rgba(52,211,153,.4);  color:#34d399; }
        .verdict-good   { background:rgba(163,230,53,.12);  border-color:rgba(163,230,53,.4);  color:#a3e635; }
        .verdict-fair   { background:rgba(251,191,36,.12);  border-color:rgba(251,191,36,.4);  color:#fbbf24; }
        .verdict-poor   { background:rgba(248,113,113,.12); border-color:rgba(248,113,113,.4); color:#f87171; }
        .verdict-none   { background:rgba(100,116,139,.12); border-color:rgba(100,116,139,.4); color:#94a3b8; }

        /* ── Metric card glow ────────────── */
        .metric-teal { box-shadow: 0 0 28px -6px rgba(45,212,191,.18); }
        .metric-amber{ box-shadow: 0 0 28px -6px rgba(251,191,36,.15); }
        .metric-green{ box-shadow: 0 0 28px -6px rgba(52,211,153,.18); }
        .metric-blue { box-shadow: 0 0 28px -6px rgba(96,165,250,.15); }

        /* scroll smooth */
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="min-h-screen text-white antialiased">

<div class="mx-auto max-w-[1440px] space-y-6 px-4 py-7 sm:px-6 xl:px-10">

    {{-- ══════════════════════════════════════════════════ HEADER --}}
    <header class="flex flex-col gap-4 border-b border-white/[0.07] pb-6 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-teal-400">Rüzgar Türbini Yatırım Analizi</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Istabreeze&nbsp;<span class="text-teal-300">500W</span>
            </h1>
            <p class="mt-1.5 max-w-lg text-sm text-slate-400">Seçilen zaman aralığındaki tüm verilerin istatistiksel analizi, yatırım fizibilitesi için.</p>
        </div>

        <div class="flex items-center gap-2.5 self-start rounded-xl glass px-4 py-2.5 text-sm md:self-auto">
            <span id="live-dot" class="live-dot h-2.5 w-2.5 rounded-full bg-amber-400"></span>
            <span id="last-updated" class="text-slate-300">Veri bekleniyor…</span>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════ ANLIK STAT KARTLARI --}}
    <section class="grid gap-4 sm:grid-cols-2">

        <article class="glass relative overflow-hidden rounded-2xl p-6 shadow-xl metric-teal">
            <div class="pointer-events-none absolute inset-0"
                 style="background:radial-gradient(circle at 100% 0%,rgba(45,212,191,.09) 0%,transparent 60%)"></div>
            <div class="relative">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Anlık Rüzgar Hızı</p>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span id="current-wind-speed" class="text-[60px] font-extrabold leading-none tabular-nums tracking-tight text-white">—</span>
                    <span class="text-2xl font-medium text-teal-300">m/s</span>
                </div>
                <div class="mt-5 space-y-1.5">
                    <div class="flex justify-between text-[11px] font-medium text-slate-500">
                        <span>0 m/s (durgun)</span><span>12 m/s (tam güç)</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full" style="background:rgba(255,255,255,0.07)">
                        <div id="wind-speed-bar" class="sbar h-full rounded-full"
                             style="width:0%;background:linear-gradient(90deg,#2dd4bf,#67e8f9)"></div>
                    </div>
                </div>
            </div>
        </article>

        <article class="glass relative overflow-hidden rounded-2xl p-6 shadow-xl metric-amber">
            <div class="pointer-events-none absolute inset-0"
                 style="background:radial-gradient(circle at 100% 0%,rgba(251,191,36,.08) 0%,transparent 60%)"></div>
            <div class="relative">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Anlık Üretilen Güç</p>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span id="current-generated-power" class="text-[60px] font-extrabold leading-none tabular-nums tracking-tight text-white">—</span>
                    <span class="text-2xl font-medium text-amber-300">W</span>
                </div>
                <div class="mt-5 space-y-1.5">
                    <div class="flex justify-between text-[11px] font-medium text-slate-500">
                        <span>0 W (üretim yok)</span><span>500 W (maksimum)</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full" style="background:rgba(255,255,255,0.07)">
                        <div id="generated-power-bar" class="sbar h-full rounded-full"
                             style="width:0%;background:linear-gradient(90deg,#34d399,#fbbf24,#f97316)"></div>
                    </div>
                </div>
            </div>
        </article>
    </section>

    {{-- ══════════════════════════════════════════════════ ZAMAN ARALIK SEÇICI --}}
    <section class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Analiz Dönemi</span>
            <div class="flex flex-wrap gap-1.5" id="preset-wrap">
                <button class="pb pb-on" data-min="0">Tümü</button>
                <button class="pb" data-min="15">15 dk</button>
                <button class="pb" data-min="30">30 dk</button>
                <button class="pb" data-min="60">1 sa</button>
                <button class="pb" data-min="360">6 sa</button>
                <button class="pb" data-min="1440">24 sa</button>
                <button class="pb" data-min="10080">7 gün</button>
                <button class="pb" data-min="43200">30 gün</button>
            </div>
            <div class="mx-2 h-4 w-px bg-white/10"></div>
            <button id="btn-custom" class="pb inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Özel aralık
            </button>
        </div>
        <p class="mt-0.5 text-[11.5px] text-teal-300/90 font-medium" id="data-range-label">Veri aralığı: Bekleniyor...</p>

        <div id="custom-panel" class="flex-wrap items-end gap-3 glass rounded-xl px-5 py-4">
            <div class="flex flex-col gap-1">
                <label for="dt-from" class="text-[11px] font-medium text-slate-400">Başlangıç</label>
                <input id="dt-from" type="datetime-local"
                       class="rounded-lg border border-white/10 bg-slate-800 px-3 py-2 text-sm text-white focus:border-teal-400/50 focus:outline-none focus:ring-2 focus:ring-teal-400/20">
            </div>
            <div class="flex flex-col gap-1">
                <label for="dt-to" class="text-[11px] font-medium text-slate-400">Bitiş</label>
                <input id="dt-to" type="datetime-local"
                       class="rounded-lg border border-white/10 bg-slate-800 px-3 py-2 text-sm text-white focus:border-teal-400/50 focus:outline-none focus:ring-2 focus:ring-teal-400/20">
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

    {{-- ══════════════════════════════════════════════════ YATIRIMBİLİMSEL ANALİZ --}}
    <section id="analysis-section">

        {{-- Verdict banner --}}
        <div id="verdict-banner"
             class="verdict-none mb-5 flex flex-col gap-3 rounded-2xl border px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span id="verdict-icon" class="text-3xl">⏳</span>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-widest opacity-70">Yatırım Değerlendirmesi</p>
                    <p id="verdict-title" class="mt-0.5 text-lg font-bold">Veri Bekleniyor</p>
                </div>
            </div>
            <p id="verdict-desc" class="max-w-sm text-sm opacity-80">Analiz için yeterli veri yüklendiğinde sonuç görünecek.</p>
        </div>

        {{-- Key metrics grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Ortalama rüzgar hızı --}}
            <div class="glass rounded-2xl p-5 metric-teal">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Ort. Rüzgar Hızı</p>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span id="stat-avg-wind" class="text-4xl font-extrabold tabular-nums text-white">—</span>
                    <span class="text-lg font-medium text-teal-300">m/s</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Medyan: <span id="stat-med-wind" class="font-semibold text-slate-200">—</span> m/s</p>
                <p class="text-xs text-slate-500">Min <span id="stat-min-wind" class="text-slate-300">—</span> · Maks <span id="stat-max-wind" class="text-slate-300">—</span> m/s</p>
            </div>

            {{-- Kapasite faktörü --}}
            <div class="glass rounded-2xl p-5 metric-green">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Kapasite Faktörü</p>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span id="stat-cf" class="text-4xl font-extrabold tabular-nums text-white">—</span>
                    <span class="text-lg font-medium text-emerald-300">%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Ort. güç: <span id="stat-avg-power" class="font-semibold text-slate-200">—</span> W</p>
                <p class="text-xs text-slate-500">≥25% iyi · ≥15% kabul edilebilir</p>
            </div>

            {{-- Aktif süre --}}
            <div class="glass rounded-2xl p-5 metric-blue">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Aktif Süre</p>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span id="stat-uptime" class="text-4xl font-extrabold tabular-nums text-white">—</span>
                    <span class="text-lg font-medium text-blue-300">%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Kesim hızı ≥2.5 m/s</p>
                <p class="text-xs text-slate-500">Örnek sayısı: <span id="stat-count" class="text-slate-300">—</span></p>
            </div>

            {{-- Tahmini yıllık üretim --}}
            <div class="glass rounded-2xl p-5 metric-amber">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tahmini Yıllık Üretim</p>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span id="stat-annual-kwh" class="text-4xl font-extrabold tabular-nums text-white">—</span>
                    <span class="text-lg font-medium text-amber-300">kWh</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Dönem üretimi: <span id="stat-period-kwh" class="font-semibold text-slate-200">—</span> kWh</p>
                <p class="text-xs text-slate-500">Ortalama güce göre projeksiyon</p>
            </div>
        </div>

        {{-- Secondary stats --}}
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="glass rounded-xl p-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Std. Sapma</p>
                <p class="mt-1.5 text-2xl font-bold text-white"><span id="stat-std">—</span> <span class="text-sm font-medium text-slate-400">m/s</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Rüzgar değişkenliği</p>
            </div>
            <div class="glass rounded-xl p-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">P10 Persentil</p>
                <p class="mt-1.5 text-2xl font-bold text-white"><span id="stat-p10">—</span> <span class="text-sm font-medium text-slate-400">m/s</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Zamanın %90'ında bu değerin üstü</p>
            </div>
            <div class="glass rounded-xl p-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">P50 (Medyan)</p>
                <p class="mt-1.5 text-2xl font-bold text-white"><span id="stat-p50">—</span> <span class="text-sm font-medium text-slate-400">m/s</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Zamanın %50'sinde bu değerin üstü</p>
            </div>
            <div class="glass rounded-xl p-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">P90 Persentil</p>
                <p class="mt-1.5 text-2xl font-bold text-white"><span id="stat-p90">—</span> <span class="text-sm font-medium text-slate-400">m/s</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Zamanın %10'unda bu değerin üstü</p>
            </div>
            <div class="glass rounded-xl p-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Ort. Güç (Medyan)</p>
                <p class="mt-1.5 text-2xl font-bold text-white"><span id="stat-med-power">—</span> <span class="text-sm font-medium text-slate-400">W</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Güç medyan değeri</p>
            </div>
            <div class="glass rounded-xl p-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Maks. Güç</p>
                <p class="mt-1.5 text-2xl font-bold text-white"><span id="stat-max-power">—</span> <span class="text-sm font-medium text-slate-400">W</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Kaydedilen en yüksek</p>
            </div>
        </div>

        {{-- Wind distribution histogram + charts row --}}
        <div class="mt-5 grid gap-5 xl:grid-cols-3">

            {{-- Histogram --}}
            <div class="glass rounded-2xl xl:col-span-1">
                <div class="border-b border-white/[0.07] px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-violet-400">Hız Dağılımı</p>
                    <h3 class="mt-1 text-base font-bold text-white">Rüzgar Hızı Frekansı</h3>
                </div>
                <div class="chart-wrap-hist p-4 pt-3">
                    <canvas id="chart-hist"></canvas>
                </div>
            </div>

            {{-- Investment note --}}
            <div class="glass rounded-2xl p-6 xl:col-span-2">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Yatırım Rehberi</p>
                <h3 class="mt-1 text-base font-bold text-white">Bu Verileri Nasıl Yorumlamalıyım?</h3>
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/[0.06] p-4">
                        <p class="font-semibold text-emerald-300">🟢 Mükemmel (CF ≥ 25%)</p>
                        <p class="mt-1 text-slate-400">Yatırım kesinlikle değer. Güçlü ve tutarlı rüzgar var. Sistemi kurabilirsiniz.</p>
                    </div>
                    <div class="rounded-xl border border-lime-400/20 bg-lime-400/[0.06] p-4">
                        <p class="font-semibold text-lime-300">🟡 İyi (CF %15–25)</p>
                        <p class="mt-1 text-slate-400">Yatırım muhtemelen değer. Uzun vadede geri dönüş sağlanabilir.</p>
                    </div>
                    <div class="rounded-xl border border-amber-400/20 bg-amber-400/[0.06] p-4">
                        <p class="font-semibold text-amber-300">🟠 Orta (CF %8–15)</p>
                        <p class="mt-1 text-slate-400">Dikkatli değerlendirme gerekli. Mevsimsel veri toplamaya devam edin.</p>
                    </div>
                    <div class="rounded-xl border border-red-400/20 bg-red-400/[0.06] p-4">
                        <p class="font-semibold text-red-300">🔴 Zayıf (CF &lt; 8%)</p>
                        <p class="mt-1 text-slate-400">Yatırım riskli. Bu konumda rüzgar türbini ekonomik değil.</p>
                    </div>
                </div>
                <div class="mt-4 rounded-xl border border-white/10 bg-white/[0.04] p-4 text-sm text-slate-400">
                    <p>💡 <span class="font-semibold text-slate-300">Önemli not:</span> En az 30 günlük sürekli veri toplanması güvenilir analiz için önerilir.
                    Mevsimsel değişkenlikleri görmek için 3–12 aylık ölçüm idealdir.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════ ZAMAN GRAFİKLERİ --}}
    <section class="glass rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between border-b border-white/[0.07] px-6 py-5">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-teal-400">Rüzgar Hızı Değişimi</p>
                <h2 class="mt-1 text-xl font-bold text-white">Zaman — m/s</h2>
            </div>
            <div class="flex items-center gap-2 rounded-xl px-3.5 py-2" style="background:rgba(45,212,191,.1)">
                <span class="h-2.5 w-2.5 rounded-full" style="background:#2dd4bf"></span>
                <span class="text-sm font-bold text-teal-300">m/s</span>
            </div>
        </div>
        <div class="chart-wrap p-4 pt-2">
            <canvas id="chart-wind"></canvas>
        </div>
    </section>

    <section class="glass rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between border-b border-white/[0.07] px-6 py-5">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-amber-400">Elektrik Üretimi</p>
                <h2 class="mt-1 text-xl font-bold text-white">Zaman — Watt</h2>
            </div>
            <div class="flex items-center gap-2 rounded-xl px-3.5 py-2" style="background:rgba(251,191,36,.1)">
                <span class="h-2.5 w-2.5 rounded-full" style="background:#fbbf24"></span>
                <span class="text-sm font-bold text-amber-300">W</span>
            </div>
        </div>
        <div class="chart-wrap p-4 pt-2">
            <canvas id="chart-power"></canvas>
        </div>
    </section>

    <p class="pb-4 text-center text-[11px] text-slate-600">Veriler her 5 saniyede bir otomatik güncellenir · Istabreeze 500W güç eğrisi kullanılmaktadır.</p>
</div>

<script>
// ════════════════════════════════════════════════════════════
// CONFIG
// ════════════════════════════════════════════════════════════
const API_URL = @json(route('api.wind.chart-data'));

const C_TEAL  = '#2dd4bf', C_AMBER = '#fbbf24';
const C_GRID  = 'rgba(148,163,184,0.1)', C_TICK = 'rgba(148,163,184,0.75)';

Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.size   = 12.5;
Chart.defaults.color       = C_TICK;

// ════════════════════════════════════════════════════════════
// STATE
// ════════════════════════════════════════════════════════════
let selMinutes = 0, cfrom = null, cto = null, customMode = false, panelOpen = false;

// ════════════════════════════════════════════════════════════
// CHART FACTORY
// ════════════════════════════════════════════════════════════
function grad(ctx, top, bot) {
    const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 420);
    g.addColorStop(0, top); g.addColorStop(1, bot);
    return g;
}

function lineOpts(sugMax, unit) {
    return {
        responsive: true, maintainAspectRatio: false,
        animation : { duration: 500, easing: 'easeInOutQuart' },
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(2,6,23,0.95)',
                borderColor    : 'rgba(255,255,255,0.1)', borderWidth: 1,
                padding        : { x:16, y:12 }, cornerRadius: 12,
                titleColor     : 'rgba(148,163,184,0.85)', titleFont: { size:12 },
                bodyColor      : '#fff', bodyFont: { weight:'700', size:15 },
                callbacks: {
                    title: function(ctx) {
                        const d = new Date(ctx[0].label);
                        if (isNaN(d)) return ctx[0].label;
                        const p = n => String(n).padStart(2,'0');
                        return `${p(d.getDate())}.${p(d.getMonth()+1)}.${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
                    },
                    label: ctx => `  ${ctx.parsed.y} ${unit}`
                },
            },
        },
        scales: {
            x: {
                grid  : { color: C_GRID }, border: { display: false },
                ticks : {
                    color: C_TICK, maxRotation: 0, autoSkip: true, maxTicksLimit: 12, padding: 8,
                    callback: function(val) {
                        const lbl = this.getLabelForValue(val);
                        const d = new Date(lbl);
                        if (isNaN(d)) return lbl;
                        const p = n => String(n).padStart(2,'0');
                        return `${p(d.getDate())}.${p(d.getMonth()+1)} ${p(d.getHours())}:${p(d.getMinutes())}`;
                    }
                },
            },
            y: {
                beginAtZero: true, suggestedMax: sugMax,
                grid  : { color: C_GRID }, border: { display: false },
                ticks : { color: C_TICK, padding: 12, callback: v => `${v} ${unit}` },
            },
        },
    };
}

function mkLine(id, line, gTop, gBot, sugMax, unit) {
    const canvas = document.getElementById(id);
    const ctx    = canvas.getContext('2d');
    return new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label: unit, data: [],
            borderColor: line, backgroundColor: () => grad(ctx, gTop, gBot),
            borderWidth: 2.5, pointRadius: 0, pointHitRadius: 20,
            pointHoverRadius: 6, pointHoverBackgroundColor: line,
            pointHoverBorderColor: '#020617', pointHoverBorderWidth: 2.5,
            fill: true, tension: 0.42 }] },
        options: lineOpts(sugMax, unit),
    });
}

const windChart  = mkLine('chart-wind',  C_TEAL,  'rgba(45,212,191,0.26)',  'rgba(45,212,191,0.0)',  14,  'm/s');
const powerChart = mkLine('chart-power', C_AMBER, 'rgba(251,191,36,0.20)',  'rgba(251,191,36,0.0)',  500, 'W');

// Histogram chart
const histCtx = document.getElementById('chart-hist').getContext('2d');
const histChart = new Chart(histCtx, {
    type: 'bar',
    data: {
        labels  : [],
        datasets: [{
            data            : [],
            backgroundColor : 'rgba(139,92,246,0.55)',
            borderColor     : 'rgba(167,139,250,0.9)',
            borderWidth     : 1.5,
            borderRadius    : 5,
            borderSkipped   : false,
        }],
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        animation : { duration: 500 },
        plugins   : {
            legend : { display: false },
            tooltip: {
                backgroundColor: 'rgba(2,6,23,0.95)',
                borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1,
                padding: { x:14, y:10 }, cornerRadius: 10,
                titleColor: 'rgba(148,163,184,0.85)',
                bodyColor: '#fff', bodyFont: { weight:'700', size:14 },
                callbacks: { label: ctx => `  ${ctx.parsed.y} ölçüm` },
            },
        },
        scales: {
            x: {
                grid  : { color: C_GRID }, border: { display: false },
                ticks : { color: C_TICK, font: { size: 11 } },
            },
            y: {
                beginAtZero: true,
                grid  : { color: C_GRID }, border: { display: false },
                ticks : { color: C_TICK, padding: 8, precision: 0 },
            },
        },
    },
});

// ════════════════════════════════════════════════════════════
// STATISTICS ENGINE
// ════════════════════════════════════════════════════════════
function percentile(sortedArr, p) {
    if (sortedArr.length === 0) return 0;
    const idx = (p / 100) * (sortedArr.length - 1);
    const lo  = Math.floor(idx), hi = Math.ceil(idx);
    return +(sortedArr[lo] + (sortedArr[hi] - sortedArr[lo]) * (idx - lo)).toFixed(2);
}

function stdDev(arr, mean) {
    if (arr.length < 2) return 0;
    const variance = arr.reduce((s, v) => s + (v - mean) ** 2, 0) / arr.length;
    return +Math.sqrt(variance).toFixed(2);
}

function computeStats(rows) {
    if (!rows || rows.length === 0) return null;

    const speeds = rows.map(r => Number(r.wind_speed));
    const powers = rows.map(r => Number(r.generated_power));
    const sorted = [...speeds].sort((a, b) => a - b);
    const sortedP = [...powers].sort((a, b) => a - b);

    const n       = speeds.length;
    const avgWind = +(speeds.reduce((s,v) => s+v, 0) / n).toFixed(2);
    const avgPow  = +(powers.reduce((s,v) => s+v, 0) / n).toFixed(2);

    const activeCount = speeds.filter(v => v >= 2.5).length;
    const uptimePct   = +((activeCount / n) * 100).toFixed(1);
    const cf          = +((avgPow / 500) * 100).toFixed(1);

    // Period duration (hours) — use recorded_at timestamps if available
    let periodHours = null;
    if (rows[0]?.recorded_at && rows[n-1]?.recorded_at) {
        const ms = new Date(rows[n-1].recorded_at) - new Date(rows[0].recorded_at);
        periodHours = ms / 3_600_000;
    }

    const periodKwh = periodHours !== null
        ? +((avgPow * periodHours) / 1000).toFixed(3)
        : null;

    const annualKwh = +((avgPow * 8760) / 1000).toFixed(0);

    // Histogram bins
    const bins = [
        { label: '0–2', min: 0,   max: 2   },
        { label: '2–4', min: 2,   max: 4   },
        { label: '4–6', min: 4,   max: 6   },
        { label: '6–8', min: 6,   max: 8   },
        { label: '8–10',min: 8,   max: 10  },
        { label: '10–12',min:10,  max: 12  },
        { label: '12+', min: 12,  max: 999 },
    ];
    const histCounts = bins.map(b => speeds.filter(v => v >= b.min && v < b.max).length);

    return {
        n, avgWind, avgPow, cf, uptimePct,
        medWind : percentile(sorted, 50),
        minWind : +sorted[0].toFixed(2),
        maxWind : +sorted[n-1].toFixed(2),
        p10     : percentile(sorted, 10),
        p50     : percentile(sorted, 50),
        p90     : percentile(sorted, 90),
        std     : stdDev(speeds, avgWind),
        medPow  : percentile(sortedP, 50),
        maxPow  : +sortedP[n-1].toFixed(2),
        periodHours, periodKwh, annualKwh,
        histLabels : bins.map(b => b.label),
        histCounts,
    };
}

// ════════════════════════════════════════════════════════════
// VERDICT LOGIC
// ════════════════════════════════════════════════════════════
function getVerdict(s) {
    if (!s || s.n < 5) return {
        cls: 'verdict-none', icon: '⏳',
        title: 'Yetersiz Veri',
        desc: 'Güvenilir analiz için daha fazla ölçüm verisi bekleniyor.',
    };

    if (s.cf >= 25) return {
        cls: 'verdict-great', icon: '🟢',
        title: `Mükemmel — Yatırım Kesinlikle Değer`,
        desc: `Kapasite faktörü %${s.cf}, ortalama hız ${s.avgWind} m/s. Bu konumda rüzgar türbini kurulabilir.`,
    };
    if (s.cf >= 15) return {
        cls: 'verdict-good', icon: '🟡',
        title: `İyi — Yatırım Muhtemelen Değer`,
        desc: `Kapasite faktörü %${s.cf}. Uzun vadeli geri dönüş mümkün. Daha fazla veri toplamanız önerilir.`,
    };
    if (s.cf >= 8) return {
        cls: 'verdict-fair', icon: '🟠',
        title: `Orta — Dikkatli Değerlendirin`,
        desc: `Kapasite faktörü %${s.cf}. Mevsimsel değişkenlik kritik. Daha uzun dönem ölçüm yapın.`,
    };
    return {
        cls: 'verdict-poor', icon: '🔴',
        title: `Zayıf — Yatırım Riskli`,
        desc: `Kapasite faktörü %${s.cf}, ortalama hız ${s.avgWind} m/s. Bu konum ekonomik olarak uygun değil.`,
    };
}

// ════════════════════════════════════════════════════════════
// DOM UPDATERS
// ════════════════════════════════════════════════════════════
function $id(id) { return document.getElementById(id); }
function setTxt(id, val) { const el = $id(id); if (el) el.textContent = val; }

function updStats(rows) {
    const s = computeStats(rows);
    const v = getVerdict(s);

    // Verdict banner
    const banner = $id('verdict-banner');
    banner.className = `${v.cls} mb-5 flex flex-col gap-3 rounded-2xl border px-6 py-5 sm:flex-row sm:items-center sm:justify-between`;
    setTxt('verdict-icon', v.icon);
    setTxt('verdict-title', v.title);
    setTxt('verdict-desc', v.desc);

    if (!s) return;

    // Key metric cards
    setTxt('stat-avg-wind', s.avgWind);
    setTxt('stat-med-wind', s.medWind);
    setTxt('stat-min-wind', s.minWind);
    setTxt('stat-max-wind', s.maxWind);
    setTxt('stat-cf', s.cf);
    setTxt('stat-avg-power', s.avgPow);
    setTxt('stat-uptime', s.uptimePct);
    setTxt('stat-count', s.n.toLocaleString('tr-TR'));
    setTxt('stat-annual-kwh', s.annualKwh.toLocaleString('tr-TR'));
    setTxt('stat-period-kwh', s.periodKwh !== null ? s.periodKwh.toLocaleString('tr-TR') : '—');

    // Secondary
    setTxt('stat-std', s.std);
    setTxt('stat-p10', s.p10);
    setTxt('stat-p50', s.p50);
    setTxt('stat-p90', s.p90);
    setTxt('stat-med-power', s.medPow);
    setTxt('stat-max-power', s.maxPow);

    // Histogram
    histChart.data.labels            = s.histLabels;
    histChart.data.datasets[0].data  = s.histCounts;
    histChart.update('active');
}

function updLiveCards(last) {
    const ws = last ? Number(last.wind_speed)      : null;
    const gp = last ? Number(last.generated_power) : null;

    setTxt('current-wind-speed',      ws !== null ? ws.toFixed(2) : '—');
    setTxt('current-generated-power', gp !== null ? gp.toFixed(2) : '—');
    $id('wind-speed-bar').style.width      = ws !== null ? `${Math.min((ws/12)*100,100)}%`   : '0%';
    $id('generated-power-bar').style.width = gp !== null ? `${Math.min((gp/500)*100,100)}%` : '0%';
}

function setConn(ok, label) {
    const dot  = $id('live-dot');
    const text = $id('last-updated');
    dot.style.background = ok ? '#34d399' : '#f87171';
    text.textContent = label;
}

function updLine(chart, labels, vals) {
    chart.data.labels            = labels;
    chart.data.datasets[0].data = vals;
    chart.update('active');
}

// ════════════════════════════════════════════════════════════
// FETCH
// ════════════════════════════════════════════════════════════
function buildUrl() {
    const p = new URLSearchParams();
    if (customMode && cfrom && cto) { p.set('from', cfrom); p.set('to', cto); }
    else if (selMinutes > 0)        { p.set('minutes', selMinutes); }
    const qs = p.toString();
    return qs ? `${API_URL}?${qs}` : API_URL;
}

async function refresh() {
    try {
        const res = await fetch(buildUrl(), { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);

        const { data: rows } = await res.json();
        if (!rows) return;

        const labels = rows.map(r => r.recorded_at);
        updLine(windChart,  labels, rows.map(r => Number(r.wind_speed)));
        updLine(powerChart, labels, rows.map(r => Number(r.generated_power)));
        updStats(rows);
        updLiveCards(rows.at(-1));

        if (rows.length > 0) {
            const first = new Date(rows[0].recorded_at);
            const lastD = new Date(rows[rows.length-1].recorded_at);
            const p = n => String(n).padStart(2,'0');
            const fmt = d => `${p(d.getDate())}.${p(d.getMonth()+1)}.${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
            $id('data-range-label').textContent = `Veri Aralığı: ${fmt(first)}  —  ${fmt(lastD)}`;
        } else {
            $id('data-range-label').textContent = `Veri Aralığı: Kayıt yok`;
        }

        const last = rows.at(-1);
        setConn(true, last
            ? `Son okuma: ${last.timestamp}  ·  ${rows.length.toLocaleString('tr-TR')} kayıt`
            : 'Kayıt yok');
    } catch {
        setConn(false, 'Bağlantı hatası');
    }
}

// ════════════════════════════════════════════════════════════
// PRESET BUTTONS
// ════════════════════════════════════════════════════════════
function activatePreset(min) {
    selMinutes = min; customMode = false;
    document.querySelectorAll('#preset-wrap .pb').forEach(b => {
        b.classList.toggle('pb-on', parseInt(b.dataset.min, 10) === min);
    });
    $id('btn-custom').classList.remove('pb-co');
    closePanel(); refresh();
}

$id('preset-wrap').addEventListener('click', e => {
    const b = e.target.closest('.pb[data-min]');
    if (b) activatePreset(parseInt(b.dataset.min, 10));
});

// ════════════════════════════════════════════════════════════
// CUSTOM PANEL
// ════════════════════════════════════════════════════════════
function closePanel() {
    panelOpen = false;
    $id('custom-panel').classList.remove('open');
}

$id('btn-custom').addEventListener('click', () => {
    panelOpen = !panelOpen;
    $id('custom-panel').classList.toggle('open', panelOpen);
    $id('btn-custom').classList.toggle('pb-co', panelOpen);
    if (panelOpen && !$id('dt-from').value) {
        const now = new Date(), h24 = new Date(+now - 86400000);
        $id('dt-from').value = fmtLocal(h24);
        $id('dt-to').value   = fmtLocal(now);
    }
});

$id('btn-apply').addEventListener('click', () => {
    const fv = $id('dt-from').value, tv = $id('dt-to').value;
    const err = $id('custom-err');
    if (!fv || !tv) { err.textContent='Başlangıç ve bitiş seçin.'; err.classList.remove('hidden'); return; }
    if (new Date(fv) >= new Date(tv)) { err.textContent='Başlangıç bitiş öncesi olmalı.'; err.classList.remove('hidden'); return; }
    err.classList.add('hidden');
    cfrom = new Date(fv).toISOString();
    cto   = new Date(tv).toISOString();
    customMode = true;
    document.querySelectorAll('#preset-wrap .pb').forEach(b => b.classList.remove('pb-on'));
    refresh();
});

$id('btn-clear').addEventListener('click', () => {
    $id('dt-from').value = '';
    $id('dt-to').value   = '';
    $id('custom-err').classList.add('hidden');
    cfrom = cto = null; customMode = false;
    activatePreset(0);
});

function fmtLocal(d) {
    const p = n => String(n).padStart(2,'0');
    return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}

// ════════════════════════════════════════════════════════════
// BOOT
// ════════════════════════════════════════════════════════════
refresh();
setInterval(refresh, 5000);
</script>
</body>
</html>
