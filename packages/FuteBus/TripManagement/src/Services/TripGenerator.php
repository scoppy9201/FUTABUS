<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TripGenerator
{
    public const DAYS_AHEAD = 30;

    public function generate(object $schedule): int
    {
        if ($schedule->status !== 'active') {
            return 0;
        }

        $days      = json_decode($schedule->days_of_week, true) ?: [];
        $from      = Carbon::today()->max(Carbon::parse($schedule->start_date));
        $to        = Carbon::today()->addDays(self::DAYS_AHEAD)->min(Carbon::parse($schedule->end_date)->endOfDay());
        $companyId = DB::table('bus_companies')->where('code', 'FUTA')->value('id');
        $count     = 0;

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            if (! in_array($d->dayOfWeekIso, $days, true)) {
                continue;
            }
            $departure = $d->copy()->setTimeFromTimeString($schedule->departure_time);

            $count += DB::table('trips')->insertOrIgnore([
                'trip_schedule_id' => $schedule->id,
                'route_id'         => $schedule->route_id,
                'bus_id'           => null,
                'bus_company_id'   => $companyId,
                'departure_time'   => $departure,
                'arrival_time'     => $departure->copy()->addMinutes((int) $schedule->duration_minutes),
                'price'            => $schedule->price,
                'status'           => 'unassigned',
                'available_seats'  => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        return $count;
    }

    public function generateAll(): int
    {
        $total = 0;
        foreach (DB::table('trip_schedules')->where('status', 'active')->get() as $s) {
            $total += $this->generate($s);
        }

        return $total;
    }
}