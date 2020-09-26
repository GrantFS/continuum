<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use Continuum;
use Illuminate\Support\Collection;

class StretchedTerm
{
    protected $start;
    protected $end;
    protected $bank_holidays;
    protected $days;
    protected $closed_dates;

    public function __construct(Carbon $start_date, Carbon $end_date)
    {
        $this->bank_holidays = [];
        $this->start = $start_date;
        $this->end = $end_date;
        $this->setTermDates();
        $this->day_difference = (int) $this->getTotalTermDayDiff();
        $this->day_count = $this->countDaysInTerm();
    }

    public function getStart() : Carbon
    {
        return $this->start;
    }

    public function getEnd() : Carbon
    {
        return $this->end;
    }

    public function getBankHolidays() :  Collection
    {
        return collect($this->bank_holidays);
    }

    public function getWeekCount() : int
    {
        return (int) $this->countWeeks();
    }

    public function getWeekCountWithoutHolidays() : int
    {
        $count = $this->getWeekCount();
        $days = 0;

        /* Remove bank holidays */
        if (config('continuum.closed_bank_holidays', true)) {
            if (count($this->bank_holidays) > 0) {
                $days = $days + (count($this->bank_holidays) / 7);
            }
        }

        foreach ($this->closed_dates as $date) {
            $days = $date->isWeekend()  ? $days : $days + 1;
        }
        $count = $count - ($days / 7);

        return ceil($count);
    }

    public function setBankHoliday(Carbon $bank_holiday) : StretchedTerm
    {
        $this->bank_holidays = array_merge($this->bank_holidays, [$bank_holiday]);
        $this->bank_holidays = $this->bank_holidays;
        return $this;
    }

    public function setClosedDates(array $closed_dates) : StretchedTerm
    {
        $this->closed_dates = $closed_dates;
        return $this;
    }

    private function countWeeks() : int
    {
        return $this->start->copy()->diffInWeeks($this->end);
    }

    private function countDaysInTerm() : int
    {
        return $this->start->copy()->diffInDays($this->end) - count($this->bank_holidays);
    }

    private function getTotalTermDayDiff() : string
    {
        $dif_start = $this->start->copy()->diffInDays($this->start->copy()->startOfWeek());
        $dif_end = $this->end->copy()->diffInDays($this->end->copy()->endOfWeek()) - 2;
        $days = $dif_end + $dif_start;
        return $days;
    }

    private function setTermDates()
    {
        $date_range = Continuum::compare()->getDaysBetween($this->start, $this->end);
        $holiday_provider = Continuum::getBankHolidayProvider($this->start->year, 2);

        foreach ($date_range as $value) {
            if (!$value->isSaturday() && !$value->isSunday()) {
                if ($holiday_provider->isBankHoliday($value)) {
                    $this->setBankHoliday($value);
                } else {
                    $this->days[$value->format('Y-m-d')] = $value->dayOfWeek;
                }
            }
        }
        $this->days = collect($this->days);
    }
}
