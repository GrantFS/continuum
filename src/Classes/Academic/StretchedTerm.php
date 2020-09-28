<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use JsonSerializable;

class StretchedTerm extends Term implements JsonSerializable
{
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

    public function countWeeks() : int
    {
        return (int) $this->start->copy()->diffInWeeks($this->end);
    }

    public function countDaysInTerm()
    {
        $weeks = $this->getStart()->copy()->diffInWeeks($this->getEnd());
        $remove_days = ($weeks * 2); // remove weekends
        if (config('continuum.closed_bank_holidays', true)) {
            $remove_days += $this->getBankHolidays()->count();
        }
        return $this->getStart()->copy()->diffInDays($this->getEnd()) - $remove_days;
    }

    public function setHalfTerm()
    {
        // do nothing
    }

    public function toArray() : array
    {
        return [
            'start' => $this->getStart(),
            'end' => $this->getEnd(),
            'days' => $this->getDays(),
            'weeks' => $this->getWeeks(),
            'months' => $this->getMonths(),
            'bank_holidays' => $this->getBankHolidays(),
            'day_count' => $this->getDayCount(),
            'week_count' => $this->getWeekCount(),
            'month_count' => $this->getMonthCount(),
            'day_difference' => $this->getDayDifference(),
            'human_weeks' => $this->getHumanWeeks(),
            'half_term_active' => $this->half_term_active,
            'closed_dates' => $this->getClosedDates()
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }
}
