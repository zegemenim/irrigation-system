<?php

namespace App\Actions;

use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\TelemetryLog;
use App\Models\Valve;
use Carbon\CarbonImmutable;

class BuildIrrigationSyncResponse
{
    public function __construct(public ResolveActiveIrrigationSchedule $resolveActiveSchedule) {}

    /**
     * @param  array{pressure_bar: float, temperature_celsius?: float|null, humidity_percent?: float|null, inverter_hz: float, inverter_status: string, inverter_current: float, error_code?: int}  $telemetry
     * @return array{status: string, command: string, mode: string, target_hz: float, set_valves: array<int, bool>, safety: array{max_pressure_bar: float, emergency_stop: bool, pressure_shutdown: bool, shutdown_reason: string|null}, active_schedule: array{id: int, valve_id: int, valve_number: int|null, duration_minutes: int}|null}
     */
    public function execute(array $telemetry): array
    {
        TelemetryLog::query()->create([
            'pressure_bar' => $telemetry['pressure_bar'],
            'temperature_celsius' => $telemetry['temperature_celsius'] ?? null,
            'humidity_percent' => $telemetry['humidity_percent'] ?? null,
            'inverter_hz' => $telemetry['inverter_hz'],
            'inverter_status' => $telemetry['inverter_status'],
            'inverter_current' => $telemetry['inverter_current'],
            'error_code' => $telemetry['error_code'] ?? 0,
        ]);

        $maxSafePressureBar = $this->maxSafePressureBar();
        $emergencyStop = SystemSetting::boolValue('emergency_stop');
        $pressureShutdown = $telemetry['pressure_bar'] > $maxSafePressureBar;

        if ($emergencyStop || $pressureShutdown) {
            return $this->forceStopResponse(
                maxSafePressureBar: $maxSafePressureBar,
                emergencyStop: $emergencyStop,
                pressureShutdown: $pressureShutdown,
                pressureBar: $telemetry['pressure_bar'],
            );
        }

        $systemMode = SystemSetting::value('system_mode', 'auto');
        $systemMode = in_array($systemMode, ['auto', 'manual'], true) ? $systemMode : 'auto';
        $activeSchedule = null;
        $setValves = $systemMode === 'manual'
            ? $this->manualValveStates()
            : $this->automaticValveStates($activeSchedule);

        $targetHz = in_array(true, $setValves, true)
            ? $this->targetHz($systemMode, $activeSchedule)
            : 0.0;

        return [
            'status' => 'success',
            'command' => 'RUN_SEQUENCE',
            'mode' => $systemMode,
            'target_hz' => $targetHz,
            'set_valves' => $setValves,
            'safety' => [
                'max_pressure_bar' => $maxSafePressureBar,
                'emergency_stop' => false,
                'pressure_shutdown' => false,
                'shutdown_reason' => null,
            ],
            'active_schedule' => $activeSchedule === null ? null : [
                'id' => $activeSchedule->id,
                'valve_id' => $this->activeValve($activeSchedule)?->id ?? $activeSchedule->valve_id,
                'valve_number' => $this->activeValveNumber($activeSchedule),
                'duration_minutes' => $activeSchedule->duration_minutes,
            ],
        ];
    }

    /**
     * @return array{status: string, command: string, mode: string, target_hz: float, set_valves: array<int, bool>, safety: array{max_pressure_bar: float, emergency_stop: bool, pressure_shutdown: bool, shutdown_reason: string|null}, active_schedule: null}
     */
    private function forceStopResponse(float $maxSafePressureBar, bool $emergencyStop, bool $pressureShutdown, float $pressureBar): array
    {
        Valve::query()->update([
            'is_active' => false,
            'last_activated_at' => null,
        ]);

        if ($pressureShutdown) {
            $this->recordPressureSafetyTrip($pressureBar, $maxSafePressureBar);
        }

        return [
            'status' => 'success',
            'command' => 'FORCE_STOP',
            'mode' => 'safe-stop',
            'target_hz' => 0.0,
            'set_valves' => $this->closedValveStates(),
            'safety' => [
                'max_pressure_bar' => $maxSafePressureBar,
                'emergency_stop' => $emergencyStop,
                'pressure_shutdown' => $pressureShutdown,
                'shutdown_reason' => $pressureShutdown ? 'pressure_limit' : 'emergency_stop',
            ],
            'active_schedule' => null,
        ];
    }

    /**
     * @return array<int, bool>
     */
    private function manualValveStates(): array
    {
        return Valve::query()
            ->orderBy('valve_number')
            ->pluck('is_active')
            ->map(fn (bool $isActive): bool => $isActive)
            ->pad($this->valveCount(), false)
            ->take($this->valveCount())
            ->values()
            ->all();
    }

    /**
     * @return array<int, bool>
     */
    private function automaticValveStates(?Schedule &$activeSchedule): array
    {
        $now = CarbonImmutable::now(config('app.timezone'));
        $activeSchedule = $this->resolveActiveSchedule->active($now);
        $states = $this->closedValveStates();

        if ($activeSchedule === null) {
            return $states;
        }

        $valveNumber = $this->activeValveNumber($activeSchedule);

        if (is_int($valveNumber) && $valveNumber >= 1 && $valveNumber <= count($states)) {
            $states[$valveNumber - 1] = true;
        }

        return $states;
    }

    /**
     * @return array<int, bool>
     */
    private function closedValveStates(): array
    {
        return array_fill(0, $this->valveCount(), false);
    }

    private function valveCount(): int
    {
        return max(
            (int) config('irrigation.valve_count', 4),
            Valve::query()->count(),
        );
    }

    private function maxSafePressureBar(): float
    {
        return SystemSetting::floatValue('max_safe_pressure_bar', (float) config('irrigation.max_safe_pressure_bar'));
    }

    private function targetHz(string $systemMode, ?Schedule $activeSchedule): float
    {
        $maxTargetHz = (float) config('irrigation.max_target_hz');

        $targetHz = $systemMode === 'manual'
            ? SystemSetting::floatValue('manual_target_hz', (float) config('irrigation.manual_target_hz'))
            : (float) ($activeSchedule?->target_hz ?? SystemSetting::floatValue('default_target_hz', (float) config('irrigation.default_target_hz')));

        return min($maxTargetHz, max(0.0, $targetHz));
    }

    private function recordPressureSafetyTrip(float $pressureBar, float $maxSafePressureBar): void
    {
        SystemSetting::put('pressure_safety_tripped', true);
        SystemSetting::put('pressure_safety_pressure_bar', round($pressureBar, 2));
        SystemSetting::put('pressure_safety_limit_bar', round($maxSafePressureBar, 2));
        SystemSetting::put('pressure_safety_tripped_at', now(config('app.timezone'))->toDateTimeString());
    }

    private function activeValveNumber(Schedule $schedule): ?int
    {
        return $this->resolveActiveSchedule->valveForDate(
            $schedule,
            CarbonImmutable::now(config('app.timezone')),
        );
    }

    private function activeValve(Schedule $schedule): ?Valve
    {
        $valveNumber = $this->activeValveNumber($schedule);

        if ($valveNumber === null) {
            return null;
        }

        return Valve::query()
            ->where('valve_number', $valveNumber)
            ->first();
    }
}
