<main wire:poll.5s="refreshDashboard" class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,#1e1b4b_0,#0f172a_34rem,#020617_100%)]">
    <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <header class="flex flex-col gap-4 border-b border-white/10 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-400 text-slate-950 shadow-lg shadow-amber-950/30">
                    <span class="text-xl font-black">💡</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-amber-200">Smart Relay Control</p>
                    <h1 class="text-2xl font-semibold text-white">Işık kontrol merkezi</h1>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white">
                    <span class="h-2.5 w-2.5 rounded-full {{ $active_relay_count > 0 ? 'bg-amber-300' : 'bg-slate-500' }}"></span>
                    {{ $active_relay_count }} / {{ count($relays) }} projektör açık
                </span>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                    ← Sulama Paneli
                </a>
                @if (auth()->user()?->is_admin)
                    <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                        Admin panel
                    </a>
                @endif
            </div>
        </header>

        {{-- Notice --}}
        @if ($notice)
            <div class="mt-4 rounded-lg border px-4 py-3 text-sm font-medium {{ $notice_tone === 'danger' ? 'border-red-400/40 bg-red-500/15 text-red-100' : 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100' }}">
                {{ $notice }}
            </div>
        @endif

        {{-- Quick Controls --}}
        <section class="mt-6 flex flex-wrap gap-3">
            <button type="button" wire:click="allOn" class="inline-flex h-12 items-center gap-2 rounded-lg bg-amber-500 px-5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                <span>☀️</span> Tümünü Aç
            </button>
            <button type="button" wire:click="allOff" wire:confirm="Tüm projektörler kapatılsın mı?" class="inline-flex h-12 items-center gap-2 rounded-lg bg-slate-700 px-5 text-sm font-bold text-white transition hover:bg-slate-600">
                <span>🌑</span> Tümünü Kapat
            </button>
        </section>

        {{-- Relay Cards --}}
        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($relays as $relay)
                <article class="relative overflow-hidden rounded-lg border p-5 transition {{ $relay['is_active'] ? 'border-amber-300/70 bg-amber-300/10 shadow-lg shadow-amber-950/30' : 'border-white/10 bg-slate-950/45 hover:bg-slate-900/70' }}">
                    {{-- Glow effect when active --}}
                    @if ($relay['is_active'])
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(251,191,36,0.15),transparent_65%)]"></div>
                    @endif

                    <div class="relative flex flex-col gap-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Röle {{ $relay['relay_number'] }}</p>
                                <h3 class="mt-1 text-lg font-semibold text-white">{{ $relay['name'] }}</h3>
                            </div>
                            <span class="text-2xl">{{ $relay['is_active'] ? '💡' : '⚫' }}</span>
                        </div>

                        <div>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold uppercase {{ $relay['is_active'] ? 'bg-amber-300 text-slate-950' : 'bg-slate-800 text-slate-300' }}">
                                {{ $relay['is_active'] ? 'Açık' : 'Kapalı' }}
                            </span>
                            <p class="mt-2 text-xs text-slate-500">{{ $relay['last_activated_at'] ?? 'Henüz açılmadı' }}</p>
                        </div>

                        <button
                            type="button"
                            wire:click="toggleRelay({{ $relay['id'] }})"
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg text-sm font-bold transition {{ $relay['is_active'] ? 'bg-amber-500 text-slate-950 hover:bg-amber-400' : 'bg-slate-700 text-white hover:bg-slate-600' }}"
                        >
                            {{ $relay['is_active'] ? 'Kapat' : 'Aç' }}
                        </button>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Motion Logs --}}
        <section class="my-8 overflow-hidden rounded-lg border border-white/10 bg-white/[0.07] backdrop-blur">
            <div class="border-b border-white/10 px-5 py-4">
                <p class="text-sm font-medium text-amber-200">Hareket Sensörleri</p>
                <h2 class="mt-0.5 text-lg font-semibold text-white">Son hareket kayıtları</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-950/50 text-slate-300">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Zaman</th>
                            <th class="px-5 py-3 font-semibold">Sensör</th>
                            <th class="px-5 py-3 font-semibold">Hareket</th>
                            <th class="px-5 py-3 font-semibold">Tetiklenen Işıklar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($recent_motion_logs as $log)
                            <tr class="text-slate-300">
                                <td class="px-5 py-3 text-slate-400">{{ $log['created_at'] }}</td>
                                <td class="px-5 py-3 font-medium text-white">{{ $log['sensor_name'] }}</td>
                                <td class="px-5 py-3">
                                    @if ($log['motion_detected'])
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-amber-400/20 px-2 py-1 text-xs font-bold text-amber-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                                            Hareket var
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-700/60 px-2 py-1 text-xs font-bold text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                            Sakin
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if (!empty($log['triggered_relay_numbers']))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($log['triggered_relay_numbers'] as $relayNumber)
                                                <span class="rounded bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-300">
                                                    Röle {{ $relayNumber }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                    Henüz hareket kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
