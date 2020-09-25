<?php

namespace Loopy\Continuum\Classes;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;

class CompareTime
{
    public function getMinutesBetween(Carbon $start_time, Carbon $end_time) : int
    {
        return $start_time->diffInMinutes($end_time);
    }

    public function getHoursBetween(Carbon $start_time, Carbon $end_time) : int
    {
        return $start_time->diffInHours($end_time);
    }

    public function getDaysBetween(Carbon $start_date, Carbon $end_date) : DatePeriod
    {
        return new \DatePeriod($start_date, CarbonInterval::day(), $end_date);
    }

    public function getWeeksBetween(Carbon $start, Carbon $end) : DatePeriod
    {
        return new \DatePeriod($start, CarbonInterval::week(), $end);
    }

    public function getMonthsBetween(Carbon $start, Carbon $end) : DatePeriod
    {
        return new \DatePeriod($start, CarbonInterval::month(), $end);
    }
}
