<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use Continuum;
use Illuminate\Support\Collection;

class StretchedTerm extends Term
{
    protected $start;
    protected $end;
    protected $bank_holidays = [];
    protected $days;
    protected $closed_dates;

    public function __construct(Carbon $start_date, Carbon $end_date)
    {
        $this->start = $start_date;
        $this->end = $end_date;
        $this->setTermDates();
        $this->day_difference = $this->getTotalTermDayDiff();
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

    public function getWeekCountWithoutHolidays()
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

    public function countWeeks()
    {
        return $this->start->copy()->diffInWeeks($this->end);
    }

    public function countDaysInTerm()
    {
        return $this->start->copy()->diffInDays($this->end) - count($this->bank_holidays);
    }

    public function setHalfTerm()
    {
        //
    }
}
