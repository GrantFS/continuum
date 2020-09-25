<?php

namespace Loopy\Continuum\Classes;

use Continuum;
use Carbon\Carbon;

class ConvertTime
{
    const DAYS = [0 =>'Sunday', 1=>'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

    public function dayNumberToName(int $day_number) : string
    {
        return self::DAYS[$day_number];
    }

    public function monthNumberToName(string $month_number) : string
    {
        return date("F", mktime(0, 0, 0, $month_number, 1));
    }

    public function monthSelectToDate(string $month_year, bool $first_weekday = false) : Carbon
    {
        $now = (Carbon::createFromFormat('Y-m', $month_year))->startOfMonth();
        return $first_weekday ? Continuum::firstWeekOfMonth($now) : $now;
    }

    public function decimalTimeToDate(string $time) : Carbon
    {
        $hours = floor($time);
        $seconds = ($time * 3600) - ($hours * 3600);
        $minutes = floor($seconds / 60);

        return Continuum::createTime($hours, $minutes);
    }

    public function toMinute(Carbon $time) : int
    {
        return $time->format('i');
    }

    public function toHour(Carbon $time) : int
    {
        return $time->format('h');
    }
}
