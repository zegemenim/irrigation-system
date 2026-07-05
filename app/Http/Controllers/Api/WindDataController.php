<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWindDataRequest;
use App\Models\WindLog;
use Illuminate\Http\JsonResponse;

class WindDataController extends Controller
{
    public function store(StoreWindDataRequest $request): JsonResponse
    {
        $windSpeed = (float) $request->validated('wind_speed');

        $windLog = WindLog::query()->create([
            'wind_speed' => $windSpeed,
            'generated_power' => $this->generatedPower($windSpeed),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->serializeWindLog($windLog),
        ], 201);
    }

    public function chartData(): JsonResponse
    {
        $windLogs = WindLog::query()
            ->latest('id')
            ->limit(30)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (WindLog $windLog): array => $this->serializeWindLog($windLog));

        return response()->json([
            'data' => $windLogs,
        ]);
    }

    private function generatedPower(float $windSpeed): float
    {
        if ($windSpeed < 2.5) {
            return 0.0;
        }

        if ($windSpeed > 12.0) {
            return 500.0;
        }

        return 500 * pow(($windSpeed - 2.5) / 9.5, 3);
    }

    /**
     * @return array{id: int|null, wind_speed: float, generated_power: float, timestamp: string|null, recorded_at: string|null}
     */
    private function serializeWindLog(WindLog $windLog): array
    {
        return [
            'id' => $windLog->id,
            'wind_speed' => round($windLog->wind_speed, 2),
            'generated_power' => round($windLog->generated_power, 2),
            'timestamp' => $windLog->created_at?->timezone(config('app.timezone'))->format('H:i:s'),
            'recorded_at' => $windLog->created_at?->toISOString(),
        ];
    }
}
