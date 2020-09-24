<?php

namespace Loopy\Continuum\Services;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Loopy\Continuum\Classes\BankHolidayProvider;

class Continuum
{
    const DAYS = [0 =>'Sunday', 1=>'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

    public function convertToMonthName(string $month_number) : string
    {
        return date("F", mktime(0, 0, 0, $month_number, 1));
    }

    public function convertToDayName(int $day_number) : string
    {
        return self::DAYS[$day_number];
    }

    public function getMonthsBetween(Carbon $start, Carbon $end) : \DatePeriod
    {
        return new \DatePeriod($start, CarbonInterval::month(), $end);
    }

    public function getWeeksBetween(Carbon $start, Carbon $end) : \DatePeriod
    {
        return new \DatePeriod($start, CarbonInterval::week(), $end);
    }

    public function getDaysBetween(Carbon $start_date, Carbon $end_date) : \DatePeriod
    {
        return new \DatePeriod($start_date, CarbonInterval::day(), $end_date);
    }

    public function getMonthsRange() : \DatePeriod
    {
        return new \DatePeriod(Carbon::now()->subMonths(6), CarbonInterval::week(), Carbon::now()->addMonths(6));
    }

    public function getWeeksFor(Carbon $start_of_month) : \DatePeriod
    {
        $first_week = $this->firstWeekOfMonth($start_of_month);
        $last_week = $this->lastWeekOfMonth($start_of_month);
        return $this->getWeeksBetween($first_week, $last_week);
    }

    public function get7DatesFrom(Carbon $start_date) : \DatePeriod
    {
        $end_date = $start_date->copy()->addDays(7);
        return new \DatePeriod($start_date, CarbonInterval::days(), $end_date);
    }

    public function firstWeekOfMonth(Carbon $start_of_month) : Carbon
    {
        return Carbon::parse('First monday of ' . $start_of_month->format('M Y'));
    }

    public function lastWeekOfMonth(Carbon $start_of_month) : Carbon
    {
        return Carbon::parse('Last monday of ' . $start_of_month->format('M Y'))->endOfWeek();
    }

    public function convertMonthSelectToDate(string $month_year, bool $first_weekday = false) : Carbon
    {
        $now = (Carbon::createFromFormat('Y-m', $month_year))->startOfMonth();
        return $first_weekday ? $this->firstWeekOfMonth($now) : $now;
    }

    public function monthStart(int $month, int $year) : Carbon
    {
        $string_date = $year . '-' . $month;
        return (Carbon::createFromFormat('Y-m', $string_date))->startOfMonth();
    }

    public function monthEnd(int $month, int $year) : Carbon
    {
        $string_date = $year . '-' . $month . '-01';
        return (Carbon::createFromFormat('Y-m', $string_date))->endOfMonth();
    }

    public function getNextDate(string $day_of_month) : Carbon
    {
        $month = Carbon::now()->format('F Y');
        $due_date = Carbon::parse($day_of_month . ' ' .$month);
        if ($due_date->lte(Carbon::now())) {
            $due_date->addMonths(1);
        }

        return $due_date;
    }

    public function isDay(int $day_of_week, Carbon $compare_date) : bool
    {
        if ($compare_date->dayOfWeek == $day_of_week) {
            return true;
        }
        return false;
    }

    public function getDatesBetween(string $start_date, string $end_date) : array
    {
        $range = $this->getDaysBetween(Carbon::parse($start_date), Carbon::parse($end_date));
        $data = [];
        foreach ($range as $date) {
            $data[] = $date;
        }
        return $data;
    }

    public function getYearSelect(int $number_of_years = 3, $tax = false) : array
    {
        $date3 = Carbon::parse('last year');
        $date = Carbon::now();
        $date2 = Carbon::parse('next year');

        $years = [
            $date3->year => ($tax ?  $date3->year . '/' . $date3->copy()->addyear()->year : $date3->year),
            $date->year => ($tax ?  $date->year . '/' . $date->copy()->addyear()->year : $date->year),
            $date2->year => ($tax ?  $date2->year . '/' . $date2->copy()->addyear()->year : $date2->year),
        ];
        if ($number_of_years > 3) {
            $count = 3;
            while ($count < $number_of_years) {
                $result = $date->copy()->addYears($count)->year;
                if ($tax) {
                    $result = $date->copy()->addYears($count)->year . '/' . $date->copy()->addYears($count)->addyear()->year;
                }
                $years = array_add($years, $date->copy()->addYears($count)->year, $result);
                $count++;
            }
        }
        return $years;
    }

    public function getMonths() : array
    {
        $months = $this->getDaysBetween(Carbon::parse('1st January'), Carbon::parse('31st December'));
        $month_range = [];
        foreach ($months as $month) {
            $month_range[$month->format('m')] = $month->format('F');
        }
        return $month_range;
    }

    public function getBankHolidayProvider(string $year, int $number_of_years = 1) : BankHolidayProvider
    {
        return new BankHolidayProvider($year, $number_of_years);
    }
}
