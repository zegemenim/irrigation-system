<main wire:poll.5s="refreshDashboard" class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,#134e4a_0,#0f172a_34rem,#020617_100%)]">
    <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-4 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-white/10 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-400 text-slate-950 shadow-lg shadow-teal-950/30">
                    <span class="text-xl font-black">SI</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-teal-200">Smart Solar Irrigation</p>
                    <h1 class="text-2xl font-semibold text-white">Sulama kontrol merkezi</h1>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white">
                    <span class="h-2.5 w-2.5 rounded-full {{ $pressure_safety_tripped || $emergency_stop ? 'bg-red-400' : ($system_mode === 'manual' ? 'bg-amber-300' : 'bg-emerald-300') }}"></span>
                    {{ $pressure_safety_tripped ? 'Basınç emniyeti' : ($emergency_stop ? 'Acil durdurma' : ($system_mode === 'manual' ? 'Manuel kontrol' : 'Otomatik sulama')) }}
                </span>

                @if (auth()->user()?->is_admin)
                    <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                        Admin panel
                    </a>
                @endif
            </div>
        </header>

        @if ($notice)
            <div class="mt-4 rounded-lg border px-4 py-3 text-sm font-medium {{ $notice_tone === 'danger' ? 'border-red-400/40 bg-red-500/15 text-red-100' : ($notice_tone === 'warning' ? 'border-amber-300/40 bg-amber-400/15 text-amber-100' : 'border-emerald-300/40 bg-emerald-400/15 text-emerald-100') }}">
                {{ $notice }}
            </div>
        @endif

        @if ($pressure_safety_tripped)
            <section class="mt-4 rounded-lg border border-red-400/50 bg-red-500/15 p-4 text-red-50">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase text-red-200">Basınçtan dolayı sistem kapandı</p>
                        <p class="mt-1 text-sm text-red-100">
                            Ölçülen basınç {{ number_format($pressure_safety_pressure_bar ?? $current_pressure, 2) }} bar.
                            Limit {{ number_format($pressure_safety_limit_bar ?? $max_safe_pressure_bar, 2) }} bar.
                            @if ($pressure_safety_tripped_at)
                                Kapanma zamanı {{ $pressure_safety_tripped_at }}.
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="resumeAutomation" class="inline-flex h-11 items-center justify-center rounded-lg bg-red-100 px-4 text-sm font-black text-red-950 transition hover:bg-white">
                        Kontrol edildi, devam et
                    </button>
                </div>
            </section>
        @endif

        <section class="grid flex-1 gap-5 py-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(340px,0.55fr)]">
            <div class="grid gap-5">
                <section class="relative overflow-hidden rounded-lg border border-white/10 bg-white/[0.07] p-4 shadow-2xl shadow-slate-950/40 backdrop-blur">
                    <div class="absolute inset-0 opacity-20 [background-image:linear-gradient(rgba(255,255,255,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.08)_1px,transparent_1px)] [background-size:38px_38px]"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-teal-200">Canlı tarla haritası</p>
                            <h2 class="mt-1 text-xl font-semibold text-white">4 bölme, tek kontrol yüzeyi</h2>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2">
                                <p class="text-slate-400">Açık bölme</p>
                                <p class="text-lg font-semibold text-white">{{ $active_valve_count }}/{{ count($valves) }}</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2">
                                <p class="text-slate-400">Son sync</p>
                                <p class="text-lg font-semibold text-white">{{ $last_telemetry_at ?? 'Yok' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($valves as $valve)
                            @php
                                $isActive = $valve_states[$valve['id']] ?? false;
                            @endphp

                            <article class="field-zone group relative min-h-60 overflow-hidden rounded-lg border p-5 transition {{ $isActive ? 'border-cyan-300/70 bg-cyan-300/12 shadow-lg shadow-cyan-950/30' : 'border-white/10 bg-slate-950/45 hover:bg-slate-900/70' }}">
                                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(20,184,166,.16),transparent_45%),linear-gradient(45deg,rgba(132,204,22,.12),transparent_60%)]"></div>
                                <div class="absolute left-5 right-5 top-20 space-y-5">
                                    <span class="field-row {{ $isActive ? 'field-row-active' : '' }}"></span>
                                    <span class="field-row {{ $isActive ? 'field-row-active field-row-delay' : '' }}"></span>
                                    <span class="field-row {{ $isActive ? 'field-row-active' : '' }}"></span>
                                </div>
                                <div class="absolute bottom-0 left-0 h-1 {{ $isActive ? 'w-full bg-cyan-300' : 'w-1/3 bg-slate-700' }} transition-all"></div>

                                <div class="relative flex min-h-52 flex-col justify-between">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Bölme {{ $valve['valve_number'] }}</p>
                                            <h3 class="mt-1 text-2xl font-semibold text-white">{{ $valve['name'] }}</h3>
                                        </div>
                                        <span class="rounded-lg px-3 py-1 text-xs font-bold uppercase {{ $isActive ? 'bg-cyan-300 text-slate-950' : 'bg-slate-800 text-slate-300' }}">
                                            {{ $isActive ? 'Suluyor' : 'Kapalı' }}
                                        </span>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                                        <div>
                                            <p class="text-sm text-slate-400">Nem değeri</p>
                                            @if ($valve['humidity_percent'] === null)
                                                <p class="mt-2 text-sm font-semibold text-amber-100">Nem değeri gelmiyor</p>
                                            @else
                                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-800">
                                                    <div class="h-full rounded-full {{ $isActive ? 'bg-cyan-300' : 'bg-lime-400' }}" style="width: {{ $valve['humidity_percent'] }}%"></div>
                                                </div>
                                                <p class="mt-2 text-sm font-semibold text-white">{{ number_format($valve['humidity_percent'], 1) }}%</p>
                                            @endif
                                            <p class="mt-2 text-sm text-slate-400">{{ $valve['last_activated_at'] ?? 'Henüz açılmadı' }}</p>
                                        </div>
                                        <button type="button" wire:click="toggleValve({{ $valve['id'] }})" @disabled($system_mode === 'auto' || $emergency_stop) class="inline-flex h-12 min-w-28 items-center justify-center rounded-lg px-4 text-sm font-bold text-white shadow-sm transition disabled:cursor-not-allowed disabled:opacity-45 {{ $isActive ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-slate-700 hover:bg-slate-600' }}">
                                            {{ $isActive ? 'Kapat' : 'Aç' }}
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-4">
                    <article class="rounded-lg border border-white/10 bg-white/[0.07] p-4 backdrop-blur">
                        <p class="text-sm text-slate-400">Basınç</p>
                        <p class="mt-2 text-3xl font-semibold {{ $current_pressure > $max_safe_pressure_bar ? 'text-red-300' : 'text-white' }}">{{ number_format($current_pressure, 2) }}</p>
                        <p class="text-sm text-slate-400">bar / limit {{ number_format($max_safe_pressure_bar, 1) }}</p>
                    </article>
                    <article class="rounded-lg border border-white/10 bg-white/[0.07] p-4 backdrop-blur">
                        <p class="text-sm text-slate-400">İnverter</p>
                        <p class="mt-2 text-3xl font-semibold text-white">{{ number_format($current_hz, 1) }}</p>
                        <p class="text-sm text-slate-400">Hz · {{ $inverter_status }}</p>
                    </article>
                    <article class="rounded-lg border border-white/10 bg-white/[0.07] p-4 backdrop-blur">
                        <p class="text-sm text-slate-400">Motor akımı</p>
                        <p class="mt-2 text-3xl font-semibold text-white">{{ number_format($inverter_current, 1) }}</p>
                        <p class="text-sm text-slate-400">A · hata {{ $error_code }}</p>
                    </article>
                    <article class="rounded-lg border border-white/10 bg-white/[0.07] p-4 backdrop-blur">
                        <p class="text-sm text-slate-400">Program</p>
                        <p class="mt-2 text-3xl font-semibold text-white">{{ $enabled_schedule_count }}</p>
                        <p class="text-sm text-slate-400">aktif zamanlama</p>
                    </article>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-lg border border-white/10 bg-white/[0.08] p-5 shadow-xl shadow-slate-950/30 backdrop-blur">
                    <h2 class="text-lg font-semibold text-white">Hızlı kontrol</h2>
                    <div class="mt-4 grid gap-3">
                        <button type="button" wire:click="toggleSystemMode" @disabled($emergency_stop) class="inline-flex h-14 items-center justify-center rounded-lg px-4 text-sm font-bold text-white transition disabled:cursor-not-allowed disabled:opacity-45 {{ $system_mode === 'manual' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-amber-500 text-slate-950 hover:bg-amber-400' }}">
                            {{ $system_mode === 'manual' ? 'Otomatik moda geç' : 'Manuel kontrole geç' }}
                        </button>
                        <button type="button" wire:click="emergencyStop" wire:confirm="Tüm valfler kapatılacak ve cihaz FORCE_STOP alacak. Devam edilsin mi?" class="inline-flex h-14 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-bold text-white transition hover:bg-red-500">
                            Acil durdur
                        </button>
                        @if ($emergency_stop)
                            <button type="button" wire:click="resumeAutomation" class="inline-flex h-14 items-center justify-center rounded-lg bg-cyan-600 px-4 text-sm font-bold text-white transition hover:bg-cyan-500">
                                Sistemi devam ettir
                            </button>
                        @endif
                    </div>
                </section>

                <form wire:submit="saveAutomationSettings" class="rounded-lg border border-white/10 bg-white/[0.08] p-5 backdrop-blur">
                    <h2 class="text-lg font-semibold text-white">Otomasyon ayarları</h2>
                    <div class="mt-4 space-y-4">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-300">Maksimum basınç</span>
                            <input type="number" step="0.01" min="0.5" max="4" wire:model="max_safe_pressure_bar" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none ring-0 transition focus:border-cyan-300">
                            <span class="mt-1 block text-xs text-slate-500">4 bar üstünde cihaz FORCE_STOP alır.</span>
                            @error('max_safe_pressure_bar') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-300">Otomatik varsayılan Hz</span>
                            <input type="number" step="0.1" min="0" max="55" wire:model="default_target_hz" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none ring-0 transition focus:border-cyan-300">
                            @error('default_target_hz') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-300">Manuel mod Hz</span>
                            <input type="number" step="0.1" min="0" max="55" wire:model="manual_target_hz" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none ring-0 transition focus:border-cyan-300">
                            @error('manual_target_hz') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                        </label>
                    </div>
                    <button type="submit" class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-lg bg-teal-500 px-4 text-sm font-bold text-slate-950 transition hover:bg-teal-400">
                        Ayarları kaydet
                    </button>
                </form>

                <section class="rounded-lg border border-white/10 bg-white/[0.08] p-5 backdrop-blur">
                    <h2 class="text-lg font-semibold text-white">Program durumu</h2>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg bg-slate-950/55 p-3">
                            <p class="text-sm text-slate-400">Şu an</p>
                            <p class="mt-1 font-semibold text-white">{{ $active_schedule['valve'] ?? 'Aktif sulama yok' }}</p>
                            @if ($active_schedule)
                                <p class="mt-1 text-sm text-cyan-200">{{ $active_schedule['remaining_minutes'] }} dk kaldı</p>
                            @endif
                        </div>
                        <div class="rounded-lg bg-slate-950/55 p-3">
                            <p class="text-sm text-slate-400">Sonraki</p>
                            <p class="mt-1 font-semibold text-white">{{ $next_schedule['valve'] ?? 'Yaklaşan program yok' }}</p>
                            @if ($next_schedule)
                                <p class="mt-1 text-sm text-slate-400">{{ $next_schedule['starts_at'] }} · {{ $next_schedule['starts_for_humans'] }}</p>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>
        </section>

        <section class="mb-5 grid gap-5 xl:grid-cols-[minmax(0,0.95fr)_minmax(360px,0.55fr)]">
            <form wire:submit="saveSchedule" class="rounded-lg border border-white/10 bg-white/[0.08] p-5 backdrop-blur">
                <div class="flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-teal-200">Dashboard programlayıcı</p>
                        <h2 class="mt-1 text-xl font-semibold text-white">Sulama döngüsü oluştur</h2>
                    </div>
                    <div class="grid grid-cols-2 overflow-hidden rounded-lg border border-white/10 bg-slate-950/60 p-1 text-sm font-bold">
                        <button type="button" wire:click="$set('schedule_mode', 'cycle')" class="rounded-md px-4 py-2 transition {{ $schedule_mode === 'cycle' ? 'bg-cyan-300 text-slate-950' : 'text-slate-300 hover:bg-white/10' }}">
                            Döngü
                        </button>
                        <button type="button" wire:click="$set('schedule_mode', 'weekly')" class="rounded-md px-4 py-2 transition {{ $schedule_mode === 'weekly' ? 'bg-cyan-300 text-slate-950' : 'text-slate-300 hover:bg-white/10' }}">
                            Haftalık
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @if ($schedule_mode === 'weekly')
                        <label class="block">
                            <span class="text-sm font-medium text-slate-300">Bölme</span>
                            <select wire:model="schedule_valve_id" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none transition focus:border-cyan-300">
                                <option value="">Bölme seç</option>
                                @foreach ($valves as $valve)
                                    <option value="{{ $valve['id'] }}">Bölme {{ $valve['valve_number'] }} - {{ $valve['name'] }}</option>
                                @endforeach
                            </select>
                            @error('schedule_valve_id') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                        </label>
                    @else
                        <label class="block">
                            <span class="text-sm font-medium text-slate-300">Döngü başlangıcı</span>
                            <input type="date" wire:model="cycle_start_date" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none transition focus:border-cyan-300">
                            @error('cycle_start_date') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                        </label>
                    @endif

                    <label class="block">
                        <span class="text-sm font-medium text-slate-300">Başlangıç saati</span>
                        <input type="time" wire:model="schedule_start_time" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none transition focus:border-cyan-300">
                        @error('schedule_start_time') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-300">Süre</span>
                        <input type="number" min="1" max="1440" step="1" wire:model="schedule_duration_minutes" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none transition focus:border-cyan-300">
                        <span class="mt-1 block text-xs text-slate-500">Dakika</span>
                        @error('schedule_duration_minutes') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-300">Bu program için Hz</span>
                        <input type="number" min="0" max="55" step="0.1" wire:model="schedule_target_hz" placeholder="{{ number_format($default_target_hz, 1) }}" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-300">
                        <span class="mt-1 block text-xs text-slate-500">Boş kalırsa otomatik varsayılan kullanılır. Üst sınır 55 Hz.</span>
                        @error('schedule_target_hz') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                    </label>
                </div>

                @if ($schedule_mode === 'cycle')
                    <div class="mt-5 rounded-lg border border-cyan-300/20 bg-cyan-300/10 p-4">
                        <div class="grid gap-4 md:grid-cols-[140px_1fr]">
                            <label class="block">
                                <span class="text-sm font-medium text-cyan-100">Döngü aralığı</span>
                                <input type="number" min="1" max="30" step="1" wire:model="cycle_interval_days" class="mt-1 block h-11 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 text-white outline-none transition focus:border-cyan-300">
                                @error('cycle_interval_days') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                            </label>
                            <div>
                                <span class="text-sm font-medium text-cyan-100">Bölme sırası</span>
                                <div class="mt-1 grid grid-cols-4 gap-2">
                                    @foreach ([0, 1, 2, 3] as $index)
                                        <label class="block">
                                            <span class="sr-only">Sıra {{ $index + 1 }}</span>
                                            <select wire:model="cycle_valve_order.{{ $index }}" class="block h-11 w-full rounded-lg border border-white/10 bg-slate-950/80 px-2 text-white outline-none transition focus:border-cyan-300">
                                                @foreach ($valves as $valve)
                                                    <option value="{{ $valve['valve_number'] }}">{{ $valve['valve_number'] }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-sm text-cyan-100/80">Örnek: 1, 2, 3, 4 seçilirse beşinci sulama tekrar 1. bölmeden başlar.</p>
                                @error('cycle_valve_order') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                                @error('cycle_valve_order.*') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-5 rounded-lg border border-white/10 bg-slate-950/45 p-4">
                        <span class="text-sm font-medium text-slate-300">Günler</span>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ([
                                'monday' => 'Pzt',
                                'tuesday' => 'Sal',
                                'wednesday' => 'Çar',
                                'thursday' => 'Per',
                                'friday' => 'Cum',
                                'saturday' => 'Cmt',
                                'sunday' => 'Paz',
                            ] as $day => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-200">
                                    <input type="checkbox" wire:model="schedule_days_of_week" value="{{ $day }}" class="rounded border-white/20 bg-slate-950 text-cyan-400 focus:ring-cyan-300">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('schedule_days_of_week') <span class="mt-1 block text-sm text-red-300">{{ $message }}</span> @enderror
                    </div>
                @endif

                <button type="submit" class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-lg bg-cyan-300 px-4 text-sm font-black text-slate-950 transition hover:bg-cyan-200">
                    Programı oluştur
                </button>
            </form>

            <section class="rounded-lg border border-white/10 bg-white/[0.08] p-5 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-teal-200">Aktif planlar</p>
                        <h2 class="mt-1 text-xl font-semibold text-white">Dashboard programları</h2>
                    </div>
                    <span class="rounded-lg bg-slate-950/60 px-3 py-2 text-sm font-bold text-white">{{ count($schedules) }}</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($schedules as $schedule)
                        <article class="rounded-lg border border-white/10 bg-slate-950/45 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-bold uppercase {{ $schedule['mode'] === 'cycle' ? 'bg-cyan-300 text-slate-950' : 'bg-lime-300 text-slate-950' }}">
                                        {{ $schedule['mode'] === 'cycle' ? 'Döngü' : 'Haftalık' }}
                                    </span>
                                    <h3 class="mt-2 font-semibold text-white">{{ $schedule['title'] }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-400">{{ $schedule['detail'] }}</p>
                                </div>
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $schedule['is_enabled'] ? 'bg-emerald-300' : 'bg-slate-600' }}"></span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <button type="button" wire:click="toggleSchedule({{ $schedule['id'] }})" class="inline-flex h-10 items-center justify-center rounded-lg border border-white/10 px-3 text-sm font-bold text-white transition hover:bg-white/10">
                                    {{ $schedule['is_enabled'] ? 'Pasifleştir' : 'Aktifleştir' }}
                                </button>
                                <button type="button" wire:click="deleteSchedule({{ $schedule['id'] }})" wire:confirm="Bu program silinsin mi?" class="inline-flex h-10 items-center justify-center rounded-lg bg-red-500/20 px-3 text-sm font-bold text-red-100 transition hover:bg-red-500/30">
                                    Sil
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-white/15 bg-slate-950/35 px-4 py-8 text-center text-sm text-slate-400">
                            Henüz dashboard programı yok.
                        </div>
                    @endforelse
                </div>
            </section>
        </section>

        <section class="mb-5 overflow-hidden rounded-lg border border-white/10 bg-white/[0.07] backdrop-blur">
            <div class="border-b border-white/10 px-5 py-4">
                <h2 class="text-lg font-semibold text-white">Son telemetri</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-slate-950/50 text-slate-300">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Zaman</th>
                            <th class="px-5 py-3 font-semibold">Basınç</th>
                            <th class="px-5 py-3 font-semibold">Sıcaklık</th>
                            <th class="px-5 py-3 font-semibold">Nem</th>
                            <th class="px-5 py-3 font-semibold">Hz</th>
                            <th class="px-5 py-3 font-semibold">Akım</th>
                            <th class="px-5 py-3 font-semibold">Durum</th>
                            <th class="px-5 py-3 font-semibold">Hata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($recent_telemetry as $telemetry)
                            <tr class="text-slate-300">
                                <td class="px-5 py-3">{{ $telemetry['created_at'] }}</td>
                                <td class="px-5 py-3 font-semibold text-white">{{ number_format($telemetry['pressure_bar'], 2) }} bar</td>
                                <td class="px-5 py-3">{{ $telemetry['temperature_celsius'] === null ? '-' : number_format($telemetry['temperature_celsius'], 1).' °C' }}</td>
                                <td class="px-5 py-3">{{ $telemetry['humidity_percent'] === null ? '-' : number_format($telemetry['humidity_percent'], 1).'%' }}</td>
                                <td class="px-5 py-3">{{ number_format($telemetry['inverter_hz'], 1) }}</td>
                                <td class="px-5 py-3">{{ number_format($telemetry['inverter_current'], 1) }} A</td>
                                <td class="px-5 py-3">{{ $telemetry['inverter_status'] }}</td>
                                <td class="px-5 py-3">{{ $telemetry['error_code'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-400">Henüz telemetri alınmadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
