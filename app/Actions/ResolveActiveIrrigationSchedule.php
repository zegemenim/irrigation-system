<?php

namespace App\Actions;

use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ResolveActiveIrrigationSchedule
{
    public function active(CarbonImmutable $now): ?Schedule
    {
        return $this->enabledSchedules()
            ->first(fn (Schedule $schedule): bool => $this->isActive($schedule, $now));
    }

    /**
     * @return array{schedule: Schedule, starts_at: CarbonImmutable}|null
     */
    public function next(CarbonImmutable $now): ?array
    {
        $schedules = $this->enabledSchedules();

        for ($dayOffset = 0; $dayOffset < 8; $dayOffset++) {
            $date = $now->addDays($dayOffset);

            foreach ($schedules as $schedule) {
                if (! $this->runsOnDate($schedule, $date)) {
                    continue;
                }

                $startsAt = $this->startAtForDate($schedule, $date);

                if ($startsAt->greaterThan($now)) {
                    return [
                        'schedule' => $schedule,
                        'starts_at' => $startsAt,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Schedule>
     */
    private function enabledSchedules(): Collection
    {
        return Schedule::query()
            ->with('valve')
            ->where('is_enabled', true)
            ->whereHas('valve')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();
    }

    private function isActive(Schedule $schedule, CarbonImmutable $now): bool
    {
        $startToday = $this->startAtForDate($schedule, $now);
        $startYesterday = $this->startAtForDate($schedule, $now->subDay());

        return ($this->runsOnDate($schedule, $now) && $this->isWithinWindow($now, $startToday, $schedule->duration_minutes))
            || ($this->runsOnDate($schedule, $now->subDay()) && $this->isWithinWindow($now, $startYesterday, $schedule->duration_minutes));
    }

    private function isWithinWindow(CarbonImmutable $now, CarbonImmutable $startsAt, int $durationMinutes): bool
    {
        return $now->greaterThanOrEqualTo($startsAt)
            && $now->lessThan($startsAt->addMinutes($durationMinutes));
    }

    private function runsOnDate(Schedule $schedule, CarbonImmutable $date): bool
    {
        if ($schedule->mode === 'cycle') {
            return $this->cycleValveForDate($schedule, $date) !== null;
        }

        return in_array(strtolower($date->englishDayOfWeek), $schedule->days_of_week ?? [], true);
    }

    private function startAtForDate(Schedule $schedule, CarbonImmutable $date): CarbonImmutable
    {
        $time = CarbonImmutable::parse((string) $schedule->start_time, config('app.timezone'));

        return $date->setTime($time->hour, $time->minute);
    }

    public function valveForDate(Schedule $schedule, CarbonImmutable $date): ?int
    {
        if ($schedule->mode === 'cycle') {
            return $this->cycleValveForDate($schedule, $date);
        }

        return $schedule->valve?->valve_number;
    }

    private function cycleValveForDate(Schedule $schedule, CarbonImmutable $date): ?int
    {
        $startDate = $schedule->cycle_start_date === null
            ? null
            : CarbonImmutable::parse($schedule->cycle_start_date, config('app.timezone'))->startOfDay();

        if ($startDate === null || $date->startOfDay()->lessThan($startDate)) {
            return null;
        }

        $order = collect($schedule->cycle_valve_order ?? [])
            ->map(fn (mixed $valveNumber): int => (int) $valveNumber)
            ->filter(fn (int $valveNumber): bool => $valveNumber > 0)
            ->values();

        if ($order->isEmpty()) {
            return null;
        }

        $intervalDays = max(1, (int) $schedule->cycle_interval_days);
        $daysSinceStart = $startDate->diffInDays($date->startOfDay());
        $cycleIndex = (int) floor($daysSinceStart / $intervalDays) % $order->count();

        return $order[$cycleIndex];
    }
}
