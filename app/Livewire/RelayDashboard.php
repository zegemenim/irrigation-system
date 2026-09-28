<?php

namespace App\Livewire;

use App\Models\MotionLog;
use App\Models\Relay;
use App\Models\Sensor;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class RelayDashboard extends Component
{
    public ?string $notice = null;

    public string $notice_tone = 'success';

    /**
     * @var array<int, array{id: int, relay_number: int, name: string, is_active: bool, last_activated_at: ?string}>
     */
    public array $relays = [];

    public int $active_relay_count = 0;

    /**
     * @var array<int, array{id: int, sensor_number: int, sensor_name: string, motion_detected: bool, triggered_relay_numbers: array<int, int>, created_at: string}>
     */
    public array $recent_motion_logs = [];

    public function mount(): void
    {
        $this->ensureDefaultRecords();
        $this->loadDashboardData();
    }

    public function render(): View
    {
        return view('livewire.relay-dashboard')
            ->layout('components.layouts.app', [
                'title' => 'Işık Kontrol Paneli',
            ]);
    }

    public function loadDashboardData(): void
    {
        $relays = $this->orderedRelays();

        $this->active_relay_count = $relays->where('is_active', true)->count();

        $this->relays = $relays
            ->map(fn (Relay $relay): array => [
                'id' => $relay->id,
                'relay_number' => $relay->relay_number,
                'name' => $relay->name,
                'is_active' => $relay->is_active,
                'last_activated_at' => $this->formatDateTime($relay->last_activated_at),
            ])
            ->all();

        $this->loadRecentMotionLogs();
    }

    public function toggleRelay(int $relayId): void
    {
        $relay = Relay::query()->findOrFail($relayId);
        $newState = ! $relay->is_active;

        $relay->update([
            'is_active' => $newState,
            'last_activated_at' => $newState ? now() : null,
        ]);

        $this->showNotice($newState ? "{$relay->name} açıldı." : "{$relay->name} kapatıldı.");
        $this->loadDashboardData();
    }

    public function allOn(): void
    {
        Relay::query()->update([
            'is_active' => true,
            'last_activated_at' => now(),
        ]);

        $this->showNotice('Tüm projektörler açıldı.');
        $this->loadDashboardData();
    }

    public function allOff(): void
    {
        Relay::query()->update([
            'is_active' => false,
            'last_activated_at' => null,
        ]);

        $this->showNotice('Tüm projektörler kapatıldı.');
        $this->loadDashboardData();
    }

    public function refreshDashboard(): void
    {
        $this->loadDashboardData();
    }

    private function loadRecentMotionLogs(): void
    {
        $relayNumbersById = Relay::query()
            ->pluck('relay_number', 'id')
            ->all();

        $this->recent_motion_logs = MotionLog::query()
            ->with('sensor')
            ->latest('created_at')
            ->limit(30)
            ->get()
            ->map(function (MotionLog $log) use ($relayNumbersById): array {
                $triggeredRelayNumbers = collect($log->triggered_relay_ids ?? [])
                    ->map(fn (int $id): ?int => $relayNumbersById[$id] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => $log->id,
                    'sensor_number' => $log->sensor->sensor_number,
                    'sensor_name' => $log->sensor->name,
                    'motion_detected' => $log->motion_detected,
                    'triggered_relay_numbers' => $triggeredRelayNumbers,
                    'created_at' => $this->formatDateTime($log->created_at) ?? 'Bilinmiyor',
                ];
            })
            ->all();
    }

    private function ensureDefaultRecords(): void
    {
        $relayCount = (int) config('relay.relay_count', 8);
        $sensorCount = (int) config('relay.sensor_count', 2);

        foreach (range(1, $relayCount) as $relayNumber) {
            Relay::query()->firstOrCreate(
                ['relay_number' => $relayNumber],
                ['name' => "Projektör {$relayNumber}", 'is_active' => false],
            );
        }

        foreach (range(1, $sensorCount) as $sensorNumber) {
            Sensor::query()->firstOrCreate(
                ['sensor_number' => $sensorNumber],
                ['name' => "Hareket Sensörü {$sensorNumber}"],
            );
        }
    }

    /**
     * @return Collection<int, Relay>
     */
    private function orderedRelays(): Collection
    {
        return Relay::query()
            ->orderBy('relay_number')
            ->get();
    }

    private function formatDateTime(?CarbonInterface $date): ?string
    {
        return $date?->timezone(config('app.timezone'))->format('d.m.Y H:i');
    }

    private function showNotice(string $message, string $tone = 'success'): void
    {
        $this->notice = $message;
        $this->notice_tone = $tone;
    }
}
