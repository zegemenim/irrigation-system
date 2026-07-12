<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Istabreeze 500W Ruzgar Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="min-h-screen bg-[radial-gradient(circle_at_top_left,#0f766e_0,#0f172a_34rem,#020617_100%)]">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-5 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-4 border-b border-white/10 pb-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-200">Canli ruzgar izleme</p>
                    <h1 class="mt-2 text-3xl font-semibold text-white sm:text-4xl">Istabreeze 500W</h1>
                </div>
                <div class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/[0.07] px-4 py-3 text-sm text-slate-300 backdrop-blur">
                    <span id="connection-indicator" class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                    <span id="last-updated">Veri bekleniyor</span>
                </div>
            </header>

            <section class="grid gap-4 md:grid-cols-2">
                <article class="rounded-lg border border-cyan-300/20 bg-white/[0.08] p-5 shadow-xl shadow-slate-950/30 backdrop-blur">
                    <p class="text-sm font-medium text-slate-400">Anlik Ruzgar Hizi</p>
                    <div class="mt-4 flex items-end gap-3">
                        <p id="current-wind-speed" class="text-5xl font-semibold tracking-normal text-white">0.00</p>
                        <p class="pb-2 text-lg font-medium text-cyan-200">m/s</p>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-900">
                        <div id="wind-speed-bar" class="h-full w-0 rounded-full bg-cyan-300 transition-all duration-500"></div>
                    </div>
                </article>

                <article class="rounded-lg border border-emerald-300/20 bg-white/[0.08] p-5 shadow-xl shadow-slate-950/30 backdrop-blur">
                    <p class="text-sm font-medium text-slate-400">Anlik Uretilen Guc</p>
                    <div class="mt-4 flex items-end gap-3">
                        <p id="current-generated-power" class="text-5xl font-semibold tracking-normal text-white">0.00</p>
                        <p class="pb-2 text-lg font-medium text-amber-200">W</p>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-900">
                        <div id="generated-power-bar" class="h-full w-0 rounded-full bg-gradient-to-r from-emerald-300 to-amber-300 transition-all duration-500"></div>
                    </div>
                </article>
            </section>

            <!-- Zaman Aralığı Seçici -->
            <section class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Zaman Aralığı:</span>
                <div class="flex flex-wrap gap-2" id="time-range-buttons">
                    <button data-minutes="2"  class="time-range-btn rounded-md border border-white/10 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.14]">2 dk</button>
                    <button data-minutes="10" class="time-range-btn rounded-md border border-white/10 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.14]">10 dk</button>
                    <button data-minutes="30" class="time-range-btn rounded-md border border-white/10 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.14]">30 dk</button>
                    <button data-minutes="60" class="time-range-btn rounded-md border border-white/10 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.14]">1 sa</button>
                    <button data-minutes="360" class="time-range-btn rounded-md border border-white/10 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.14]">6 sa</button>
                    <button data-minutes="0"  class="time-range-btn active rounded-md border border-cyan-300/40 bg-cyan-300/[0.13] px-3 py-1.5 text-xs font-semibold text-cyan-200 transition">Tümü</button>
                </div>
            </section>

            <section class="grid flex-1 gap-5 xl:grid-cols-2">
                <article class="min-h-[22rem] rounded-lg border border-white/10 bg-slate-950/55 p-4 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-cyan-200">Ruzgar Hizi Degisimi</p>
                            <h2 class="mt-1 text-xl font-semibold text-white">Zaman / m/s</h2>
                        </div>
                        <span class="rounded-lg bg-cyan-300/10 px-3 py-1 text-sm font-semibold text-cyan-100">m/s</span>
                    </div>
                    <div class="mt-5 h-[19rem]">
                        <canvas id="wind-speed-chart"></canvas>
                    </div>
                </article>

                <article class="min-h-[22rem] rounded-lg border border-white/10 bg-slate-950/55 p-4 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-emerald-200">Elektrik Uretimi</p>
                            <h2 class="mt-1 text-xl font-semibold text-white">Zaman / Watt</h2>
                        </div>
                        <span class="rounded-lg bg-amber-300/10 px-3 py-1 text-sm font-semibold text-amber-100">W</span>
                    </div>
                    <div class="mt-5 h-[19rem]">
                        <canvas id="generated-power-chart"></canvas>
                    </div>
                </article>
            </section>
        </div>
    </main>

    <script>
        const chartDataUrl = @json(route('api.wind.chart-data'));
        const chartGridColor = 'rgba(148, 163, 184, 0.16)';
        const chartTickColor = 'rgba(226, 232, 240, 0.72)';

        // Seçili zaman aralığı (0 = tümü / son 30 kayıt)
        let selectedMinutes = 0;
        let refreshTimer = null;

        Chart.defaults.color = chartTickColor;
        Chart.defaults.font.family = 'ui-sans-serif, system-ui, sans-serif';

        function createLineChart(canvasId, label, borderColor, backgroundColor, suggestedMax) {
            const context = document.getElementById(canvasId);

            return new Chart(context, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label,
                        data: [],
                        borderColor,
                        backgroundColor,
                        borderWidth: 3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: borderColor,
                        fill: true,
                        tension: 0.35,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 450,
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            borderColor: 'rgba(255, 255, 255, 0.12)',
                            borderWidth: 1,
                            padding: 12,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: chartGridColor,
                            },
                            ticks: {
                                color: chartTickColor,
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 8,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax,
                            grid: {
                                color: chartGridColor,
                            },
                            ticks: {
                                color: chartTickColor,
                            },
                        },
                    },
                },
            });
        }

        const windSpeedChart = createLineChart(
            'wind-speed-chart',
            'Ruzgar Hizi',
            'rgb(34, 211, 238)',
            'rgba(34, 211, 238, 0.14)',
            14
        );

        const generatedPowerChart = createLineChart(
            'generated-power-chart',
            'Uretilen Guc',
            'rgb(251, 191, 36)',
            'rgba(16, 185, 129, 0.14)',
            500
        );

        // Zaman aralığı butonları
        document.getElementById('time-range-buttons').addEventListener('click', (e) => {
            const btn = e.target.closest('.time-range-btn');
            if (! btn) { return; }

            document.querySelectorAll('.time-range-btn').forEach((b) => {
                b.className = 'time-range-btn rounded-md border border-white/10 bg-white/[0.06] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.14]';
            });
            btn.className = 'time-range-btn active rounded-md border border-cyan-300/40 bg-cyan-300/[0.13] px-3 py-1.5 text-xs font-semibold text-cyan-200 transition';

            selectedMinutes = parseInt(btn.dataset.minutes, 10);
            refreshDashboard();
        });

        function setConnectionState(isConnected, label) {
            const indicator = document.getElementById('connection-indicator');
            const lastUpdated = document.getElementById('last-updated');

            indicator.className = `h-2.5 w-2.5 rounded-full ${isConnected ? 'bg-emerald-300' : 'bg-red-400'}`;
            lastUpdated.textContent = label;
        }

        function updateChart(chart, labels, values) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.update();
        }

        function updateStats(latestReading) {
            const windSpeed = latestReading ? Number(latestReading.wind_speed) : 0;
            const generatedPower = latestReading ? Number(latestReading.generated_power) : 0;

            document.getElementById('current-wind-speed').textContent = windSpeed.toFixed(2);
            document.getElementById('current-generated-power').textContent = generatedPower.toFixed(2);
            document.getElementById('wind-speed-bar').style.width = `${Math.min((windSpeed / 12) * 100, 100)}%`;
            document.getElementById('generated-power-bar').style.width = `${Math.min((generatedPower / 500) * 100, 100)}%`;
        }

        async function refreshDashboard() {
            try {
                const url = selectedMinutes > 0
                    ? `${chartDataUrl}?minutes=${selectedMinutes}`
                    : chartDataUrl;

                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                });

                if (! response.ok) {
                    throw new Error('Chart data request failed.');
                }

                const payload = await response.json();
                const readings = payload.data ?? [];
                const labels = readings.map((reading) => reading.timestamp);
                const windSpeeds = readings.map((reading) => Number(reading.wind_speed));
                const generatedPowers = readings.map((reading) => Number(reading.generated_power));

                updateChart(windSpeedChart, labels, windSpeeds);
                updateChart(generatedPowerChart, labels, generatedPowers);
                updateStats(readings.at(-1));
                setConnectionState(true, readings.length > 0 ? `Son okuma ${readings.at(-1).timestamp}` : 'Kayit yok');
            } catch (error) {
                setConnectionState(false, 'Baglanti hatasi');
            }
        }

        refreshDashboard();
        setInterval(refreshDashboard, 3000);
    </script>
</body>
</html>
