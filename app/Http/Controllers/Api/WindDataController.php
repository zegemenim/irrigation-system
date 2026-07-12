<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWindDataRequest;
use App\Models\WindLog;
use Carbon\Carbon;
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
            'message' => 'Veri başarıyla kaydedildi',
            'data' => $this->serializeWindLog($windLog),
        ], 201);
    }

    public function chartData(): JsonResponse
    {
        $minutes = (int) request()->query('minutes', 0);
        $from = request()->query('from');
        $to = request()->query('to');

        $query = WindLog::query()->orderBy('id');

        if ($from && $to) {
            $query->whereBetween('created_at', [
                Carbon::parse($from)->utc(),
                Carbon::parse($to)->utc(),
            ]);
        } elseif ($minutes > 0) {
            $query->where('created_at', '>=', now()->subMinutes($minutes));
        }
        // else: parametre yok → tüm kayıtlar (limit yok)

        $windLogs = $query
            ->get()
            ->map(fn (WindLog $windLog): array => $this->serializeWindLog($windLog))
            ->values();

        return response()->json([
            'data' => $windLogs,
        ]);
    }

    /**
     * Istabreeze 500W fabrika güç eğrisine göre doğrusal interpolasyon.
     *
     * Güç Eğrisi (m/s => W):
     *
     * @param  array<float, float>  $powerCurve
     */
    private function generatedPower(float $windSpeed): float
    {
        if ($windSpeed < 2.5) {
            return 0.0;
        }

        if ($windSpeed >= 12.0) {
            return 500.0;
        }

        /** @var array<float, int> $powerCurve */
        $powerCurve = [
            2.5 => 0,
            3.0 => 5,
            4.0 => 22,
            5.0 => 55,
            6.0 => 100,
            7.0 => 160,
            8.0 => 240,
            9.0 => 320,
            10.0 => 410,
            11.0 => 470,
            12.0 => 500,
        ];

        // Tam eşleşme kontrolü
        if (array_key_exists($windSpeed, $powerCurve)) {
            return (float) $powerCurve[$windSpeed];
        }

        $lowerV = 2.5;
        $lowerP = 0;
        $upperV = 12.0;
        $upperP = 500;

        foreach ($powerCurve as $curveV => $curveP) {
            if ($windSpeed > $curveV) {
                $lowerV = $curveV;
                $lowerP = $curveP;
            } elseif ($windSpeed < $curveV) {
                $upperV = $curveV;
                $upperP = $curveP;
                break;
            }
        }

        // Doğrusal interpolasyon
        $calculatedPower = $lowerP + (($windSpeed - $lowerV) * ($upperP - $lowerP) / ($upperV - $lowerV));

        return round($calculatedPower, 2);
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
