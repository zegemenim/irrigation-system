<?php

namespace App\Livewire;

use App\Actions\ResolveActiveIrrigationSchedule;
use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\TelemetryLog;
use App\Models\Valve;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class IrrigationDashboard extends Component
{
    public string $system_mode = 'auto';

    public bool $emergency_stop = false;

    public float $max_safe_pressure_bar = 6.0;

    public float $default_target_hz = 50.0;

    public float $manual_target_hz = 45.0;

    public float $current_pressure = 0.0;

    public float $current_hz = 0.0;

    public float $inverter_current = 0.0;

    public string $inverter_status = 'NO DATA';

    public int $error_code = 0;

    public ?string $last_telemetry_at = null;

    public bool $pressure_safety_tripped = false;

    public ?float $pressure_safety_pressure_bar = null;

    public ?float $pressure_safety_limit_bar = null;

    public ?string $pressure_safety_tripped_at = null;

    public ?string $notice = null;

    public string $notice_tone = 'success';

    /**
     * @var array<int, bool>
     */
    public array $valve_states = [];

    /**
     * @var array<int, array{id: int, valve_number: int, name: string, is_active: bool, last_activated_at: ?string, humidity_percent: ?float}>
     */
    public array $valves = [];

    /**
     * @var array{id: int, valve: string, remaining_minutes: int}|null
     */
    public ?array $active_schedule = null;

    /**
     * @var array{id: int, valve: string, starts_at: string, starts_for_humans: string}|null
     */
    public ?array $next_schedule = null;

    /**
     * @var array<int, array{pressure_bar: float, temperature_celsius: ?float, humidity_percent: ?float, inverter_hz: float, inverter_current: float, inverter_status: string, error_code: int, created_at: string}>
     */
    public array $recent_telemetry = [];

    public int $enabled_schedule_count = 0;

    public int $active_valve_count = 0;

    /**
     * @var array<int, array{id: int, title: string, detail: string, is_enabled: bool, mode: string, target_hz: ?float}>
     */
    public array $schedules = [];

    public string $schedule_mode = 'cycle';

    public ?int $schedule_valve_id = null;

    /**
     * @var array<int, string>
     */
    public array $schedule_days_of_week = ['monday'];

    public string $schedule_start_time = '07:00';

    public int $schedule_duration_minutes = 30;

    public ?float $schedule_target_hz = null;

    public string $cycle_start_date = '';

    public int $cycle_interval_days = 1;

    /**
     * @var array<int, int>
     */
    public array $cycle_valve_order = [1, 2, 3, 4];

    public function mount(): void
    {
        $this->ensureDefaultRecords();
        $this->cycle_start_date = now(config('app.timezone'))->toDateString();
        $this->loadDashboardData();
    }

    public function render(): View
    {
        return view('livewire.irrigation-dashboard')
            ->layout('components.layouts.app', [
                'title' => 'Sulama Kontrol Paneli',
            ]);
    }

    public function loadDashboardData(): void
    {
        $this->system_mode = SystemSetting::value('system_mode', 'auto') === 'manual' ? 'manual' : 'auto';
        $this->emergency_stop = SystemSetting::boolValue('emergency_stop');
        $this->max_safe_pressure_bar = SystemSetting::floatValue('max_safe_pressure_bar', (float) config('irrigation.max_safe_pressure_bar'));
        $this->default_target_hz = SystemSetting::floatValue('default_target_hz', (float) config('irrigation.default_target_hz'));
        $this->manual_target_hz = SystemSetting::floatValue('manual_target_hz', (float) config('irrigation.manual_target_hz'));
        $this->pressure_safety_tripped = SystemSetting::boolValue('pressure_safety_tripped');
        $this->pressure_safety_pressure_bar = SystemSetting::floatValue('pressure_safety_pressure_bar', 0.0) ?: null;
        $this->pressure_safety_limit_bar = SystemSetting::floatValue('pressure_safety_limit_bar', $this->max_safe_pressure_bar);
        $this->pressure_safety_tripped_at = $this->formatDateTimeFromString(SystemSetting::value('pressure_safety_tripped_at'));

        $latestTelemetry = TelemetryLog::query()
            ->latest('created_at')
            ->first();

        $this->current_pressure = (float) ($latestTelemetry?->pressure_bar ?? 0.0);
        $this->current_hz = (float) ($latestTelemetry?->inverter_hz ?? 0.0);
        $this->inverter_current = (float) ($latestTelemetry?->inverter_current ?? 0.0);
        $this->inverter_status = $latestTelemetry?->inverter_status ?? 'NO DATA';
        $this->error_code = (int) ($latestTelemetry?->error_code ?? 0);
        $this->last_telemetry_at = $this->formatDateTime($latestTelemetry?->created_at);

        $valves = $this->orderedValves();

        $this->valve_states = $valves
            ->pluck('is_active', 'id')
            ->map(fn (bool $isActive): bool => $isActive)
            ->all();

        $this->valves = $valves
            ->map(fn (Valve $valve): array => [
                'id' => $valve->id,
                'valve_number' => $valve->valve_number,
                'name' => $valve->name,
                'is_active' => $valve->is_active,
                'last_activated_at' => $this->formatDateTime($valve->last_activated_at),
                'humidity_percent' => $latestTelemetry?->humidity_percent,
            ])
            ->all();

        $this->active_valve_count = $valves
            ->where('is_active', true)
            ->count();

        $this->enabled_schedule_count = Schedule::query()
            ->where('is_enabled', true)
            ->count();

        $this->loadScheduleSummary();
        $this->loadRecentTelemetry();
        $this->loadSchedules();
    }

    public function toggleValve(int $valveId): void
    {
        if ($this->system_mode !== 'manual') {
            $this->showNotice('Manuel moda geçmeden valf komutu gönderilemez.', 'warning');
            $this->loadDashboardData();

            return;
        }

        if ($this->emergency_stop) {
            $this->showNotice('Acil durdurma aktifken valf açılamaz.', 'danger');
            $this->loadDashboardData();

            return;
        }

        $valve = Valve::query()->findOrFail($valveId);
        $newState = ! $valve->is_active;

        $valve->update([
            'is_active' => $newState,
            'last_activated_at' => $newState ? now() : null,
        ]);

        $this->showNotice($newState ? "{$valve->name} açıldı." : "{$valve->name} kapatıldı.");
        $this->loadDashboardData();
    }

    public function toggleSystemMode(): void
    {
        $newMode = $this->system_mode === 'manual' ? 'auto' : 'manual';

        SystemSetting::put('system_mode', $newMode);

        if ($newMode === 'auto') {
            Valve::query()->update([
                'is_active' => false,
                'last_activated_at' => null,
            ]);
        }

        $this->showNotice($newMode === 'manual' ? 'Manuel kontrol etkin.' : 'Otomatik sulama etkin.');
        $this->loadDashboardData();
    }

    public function saveAutomationSettings(): void
    {
        $validated = $this->validate([
            'max_safe_pressure_bar' => ['required', 'numeric', 'min:0.5', 'max:4'],
            'default_target_hz' => ['required', 'numeric', 'min:0', 'max:55'],
            'manual_target_hz' => ['required', 'numeric', 'min:0', 'max:55'],
        ]);

        SystemSetting::put('max_safe_pressure_bar', round((float) $validated['max_safe_pressure_bar'], 2));
        SystemSetting::put('default_target_hz', round((float) $validated['default_target_hz'], 2));
        SystemSetting::put('manual_target_hz', round((float) $validated['manual_target_hz'], 2));

        $this->showNotice('Otomasyon ayarları kaydedildi.');
        $this->loadDashboardData();
    }

    public function saveSchedule(): void
    {
        $validated = $this->validate([
            'schedule_mode' => ['required', 'in:weekly,cycle'],
            'schedule_valve_id' => ['required_if:schedule_mode,weekly', 'nullable', 'exists:valves,id'],
            'schedule_days_of_week' => ['array'],
            'schedule_days_of_week.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'schedule_start_time' => ['required', 'date_format:H:i'],
            'schedule_duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'schedule_target_hz' => ['nullable', 'numeric', 'min:0', 'max:55'],
            'cycle_start_date' => ['required_if:schedule_mode,cycle', 'date'],
            'cycle_interval_days' => ['required_if:schedule_mode,cycle', 'integer', 'min:1', 'max:30'],
            'cycle_valve_order' => ['array', 'min:1'],
            'cycle_valve_order.*' => ['integer', 'min:1', 'max:4'],
        ]);

        if ($validated['schedule_mode'] === 'weekly' && empty($validated['schedule_days_of_week'])) {
            $this->showNotice('Haftalık program için en az bir gün seç.', 'warning');

            return;
        }

        $valveId = $validated['schedule_mode'] === 'weekly'
            ? $validated['schedule_valve_id']
            : Valve::query()->orderBy('valve_number')->value('id');

        if ($valveId === null) {
            $this->showNotice('Program oluşturmak için en az bir bölme kaydı gerekir.', 'danger');

            return;
        }

        Schedule::query()->create([
            'valve_id' => $valveId,
            'mode' => $validated['schedule_mode'],
            'days_of_week' => $validated['schedule_mode'] === 'weekly' ? array_values($validated['schedule_days_of_week']) : [],
            'cycle_valve_order' => $validated['schedule_mode'] === 'cycle' ? array_values($validated['cycle_valve_order']) : null,
            'cycle_start_date' => $validated['schedule_mode'] === 'cycle' ? $validated['cycle_start_date'] : null,
            'cycle_interval_days' => $validated['schedule_mode'] === 'cycle' ? $validated['cycle_interval_days'] : 1,
            'start_time' => $validated['schedule_start_time'],
            'duration_minutes' => $validated['schedule_duration_minutes'],
            'target_hz' => $validated['schedule_target_hz'],
            'is_enabled' => true,
        ]);

        $this->resetScheduleForm();
        $this->showNotice('Sulama programı dashboard üzerinden oluşturuldu.');
        $this->loadDashboardData();
    }

    public function toggleSchedule(int $scheduleId): void
    {
        $schedule = Schedule::query()->findOrFail($scheduleId);
        $schedule->update(['is_enabled' => ! $schedule->is_enabled]);

        $this->showNotice($schedule->is_enabled ? 'Program aktifleştirildi.' : 'Program pasifleştirildi.');
        $this->loadDashboardData();
    }

    public function deleteSchedule(int $scheduleId): void
    {
        Schedule::query()->findOrFail($scheduleId)->delete();

        $this->showNotice('Program silindi.');
        $this->loadDashboardData();
    }

    public function emergencyStop(): void
    {
        SystemSetting::put('emergency_stop', true);
        SystemSetting::put('system_mode', 'manual');

        Valve::query()->update([
            'is_active' => false,
            'last_activated_at' => null,
        ]);

        $this->showNotice('Acil durdurma etkin. Tüm valfler kapatıldı.', 'danger');
        $this->loadDashboardData();
    }

    public function resumeAutomation(): void
    {
        SystemSetting::put('emergency_stop', false);
        SystemSetting::put('system_mode', 'auto');
        SystemSetting::put('pressure_safety_tripped', false);

        $this->showNotice('Otomatik sulama tekrar başlatıldı.');
        $this->loadDashboardData();
    }

    public function refreshDashboard(): void
    {
        $this->loadDashboardData();
    }

    /**
     * @return Collection<int, Valve>
     */
    private function orderedValves(): Collection
    {
        return Valve::query()
            ->orderBy('valve_number')
            ->limit((int) config('irrigation.valve_count', 4))
            ->get();
    }

    private function ensureDefaultRecords(): void
    {
        SystemSetting::query()->firstOrCreate(['key' => 'system_mode'], ['value' => 'auto']);
        SystemSetting::query()->firstOrCreate(['key' => 'emergency_stop'], ['value' => '0']);
        SystemSetting::query()->firstOrCreate(['key' => 'max_safe_pressure_bar'], ['value' => (string) config('irrigation.max_safe_pressure_bar')]);
        SystemSetting::query()->firstOrCreate(['key' => 'default_target_hz'], ['value' => (string) config('irrigation.default_target_hz')]);
        SystemSetting::query()->firstOrCreate(['key' => 'manual_target_hz'], ['value' => (string) config('irrigation.manual_target_hz')]);
        SystemSetting::query()->firstOrCreate(['key' => 'pressure_safety_tripped'], ['value' => '0']);

        foreach (range(1, (int) config('irrigation.valve_count', 4)) as $valveNumber) {
            Valve::query()->firstOrCreate(
                ['valve_number' => $valveNumber],
                [
                    'name' => $this->defaultValveName($valveNumber),
                    'is_active' => false,
                    'last_activated_at' => null,
                ],
            );
        }
    }

    private function loadScheduleSummary(): void
    {
        $now = CarbonImmutable::now(config('app.timezone'));
        $resolver = app(ResolveActiveIrrigationSchedule::class);
        $activeSchedule = $resolver->active($now);
        $nextSchedule = $resolver->next($now);

        $this->active_schedule = $activeSchedule === null ? null : [
            'id' => $activeSchedule->id,
            'valve' => $this->formatValveLabel($activeSchedule),
            'remaining_minutes' => $this->remainingMinutes($activeSchedule, $now),
        ];

        $this->next_schedule = $nextSchedule === null ? null : [
            'id' => $nextSchedule['schedule']->id,
            'valve' => $this->formatValveLabel($nextSchedule['schedule']),
            'starts_at' => $this->formatDateTime($nextSchedule['starts_at']),
            'starts_for_humans' => $nextSchedule['starts_at']->diffForHumans(),
        ];
    }

    private function loadRecentTelemetry(): void
    {
        $this->recent_telemetry = TelemetryLog::query()
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (TelemetryLog $telemetryLog): array => [
                'pressure_bar' => $telemetryLog->pressure_bar,
                'temperature_celsius' => $telemetryLog->temperature_celsius,
                'humidity_percent' => $telemetryLog->humidity_percent,
                'inverter_hz' => $telemetryLog->inverter_hz,
                'inverter_current' => $telemetryLog->inverter_current,
                'inverter_status' => $telemetryLog->inverter_status,
                'error_code' => $telemetryLog->error_code,
                'created_at' => $this->formatDateTime($telemetryLog->created_at) ?? 'Bilinmiyor',
            ])
            ->all();
    }

    private function loadSchedules(): void
    {
        $this->schedules = Schedule::query()
            ->with('valve')
            ->latest('is_enabled')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Schedule $schedule): array => [
                'id' => $schedule->id,
                'title' => $this->scheduleTitle($schedule),
                'detail' => $this->scheduleDetail($schedule),
                'is_enabled' => $schedule->is_enabled,
                'mode' => $schedule->mode,
                'target_hz' => $schedule->target_hz,
            ])
            ->all();
    }

    private function formatValveLabel(Schedule $schedule): string
    {
        $valveNumber = app(ResolveActiveIrrigationSchedule::class)->valveForDate(
            $schedule,
            CarbonImmutable::now(config('app.timezone')),
        );

        if ($valveNumber === null) {
            return 'Bölme belirlenemedi';
        }

        $valve = Valve::query()
            ->where('valve_number', $valveNumber)
            ->first();

        return "Bölme {$valveNumber} - {$valve?->name}";
    }

    private function remainingMinutes(Schedule $schedule, CarbonImmutable $now): int
    {
        $time = CarbonImmutable::parse((string) $schedule->start_time, config('app.timezone'));
        $startsAt = $now->setTime($time->hour, $time->minute);

        if ($startsAt->greaterThan($now)) {
            $startsAt = $startsAt->subDay();
        }

        return max(0, (int) ceil($now->diffInSeconds($startsAt->addMinutes($schedule->duration_minutes), false) / 60));
    }

    private function defaultValveName(int $valveNumber): string
    {
        return match ($valveNumber) {
            1 => 'Kuzey Bölme',
            2 => 'Doğu Bölme',
            3 => 'Güney Bölme',
            4 => 'Batı Bölme',
            default => "Bölme {$valveNumber}",
        };
    }

    private function showNotice(string $message, string $tone = 'success'): void
    {
        $this->notice = $message;
        $this->notice_tone = $tone;
    }

    private function resetScheduleForm(): void
    {
        $this->schedule_mode = 'cycle';
        $this->schedule_valve_id = null;
        $this->schedule_days_of_week = ['monday'];
        $this->schedule_start_time = '07:00';
        $this->schedule_duration_minutes = 30;
        $this->schedule_target_hz = null;
        $this->cycle_start_date = now(config('app.timezone'))->toDateString();
        $this->cycle_interval_days = 1;
        $this->cycle_valve_order = [1, 2, 3, 4];
    }

    private function scheduleTitle(Schedule $schedule): string
    {
        if ($schedule->mode === 'cycle') {
            return 'Döngü: '.collect($schedule->cycle_valve_order ?? [])->implode(' → ');
        }

        return "Haftalık: Bölme {$schedule->valve?->valve_number} - {$schedule->valve?->name}";
    }

    private function scheduleDetail(Schedule $schedule): string
    {
        $targetHz = $schedule->target_hz === null ? 'varsayılan Hz' : number_format($schedule->target_hz, 1).' Hz';

        if ($schedule->mode === 'cycle') {
            return "{$schedule->cycle_start_date?->format('d.m.Y')} başlangıç, {$schedule->cycle_interval_days} günde bir, {$schedule->start_time?->format('H:i')} / {$schedule->duration_minutes} dk / {$targetHz}";
        }

        $days = collect($schedule->days_of_week ?? [])
            ->map(fn (string $day): string => ucfirst($day))
            ->implode(', ');

        return "{$days}, {$schedule->start_time?->format('H:i')} / {$schedule->duration_minutes} dk / {$targetHz}";
    }

    private function formatDateTime(?CarbonInterface $date): ?string
    {
        return $date?->timezone(config('app.timezone'))->format('d.m.Y H:i');
    }

    private function formatDateTimeFromString(?string $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        return $this->formatDateTime(CarbonImmutable::parse($date, config('app.timezone')));
    }
}
