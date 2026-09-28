<?php

namespace App\Actions;

use App\Models\MotionLog;
use App\Models\Relay;
use App\Models\Sensor;

class BuildRelaySyncResponse
{
    /**
     * @param  array{sensors: array<int, array{sensor_number: int, motion_detected: bool}>}  $data
     * @return array{status: string, set_relays: array<int, bool>}
     */
    public function execute(array $data): array
    {
        $relayActivations = $this->processMotionSensors($data['sensors']);

        $this->applyRelayActivations($relayActivations);

        return [
            'status' => 'success',
            'set_relays' => $this->currentRelayStates(),
        ];
    }

    /**
     * Process incoming sensor data, persist logs, and return which relay IDs to activate.
     *
     * @param  array<int, array{sensor_number: int, motion_detected: bool}>  $sensors
     * @return array<int, bool> relay_id => should_be_active
     */
    private function processMotionSensors(array $sensors): array
    {
        $relayActivations = [];

        foreach ($sensors as $sensorData) {
            $sensor = Sensor::query()
                ->with('relays')
                ->where('sensor_number', $sensorData['sensor_number'])
                ->first();

            if ($sensor === null) {
                continue;
            }

            $triggeredRelayIds = [];

            if ($sensorData['motion_detected']) {
                foreach ($sensor->relays as $relay) {
                    $triggeredRelayIds[] = $relay->id;
                    $relayActivations[$relay->id] = true;
                }
            }

            MotionLog::query()->create([
                'sensor_id' => $sensor->id,
                'motion_detected' => $sensorData['motion_detected'],
                'triggered_relay_ids' => $triggeredRelayIds ?: null,
            ]);
        }

        return $relayActivations;
    }

    /**
     * @param  array<int, bool>  $relayActivations  relay_id => should_be_active
     */
    private function applyRelayActivations(array $relayActivations): void
    {
        if (empty($relayActivations)) {
            return;
        }

        $relayIds = array_keys($relayActivations);

        Relay::query()->whereIn('id', $relayIds)->update([
            'is_active' => true,
            'last_activated_at' => now(),
        ]);
    }

    /**
     * @return array<int, bool>
     */
    private function currentRelayStates(): array
    {
        return Relay::query()
            ->orderBy('relay_number')
            ->pluck('is_active')
            ->map(fn (bool $isActive): bool => $isActive)
            ->values()
            ->all();
    }
}
