<?php

namespace Loopy\Continuum\Classes\Academic;

class StretchedTerm extends Term
{
    public function countDaysInTerm()
    {
        $weeks = $this->getStart()->copy()->diffInWeeks($this->getEnd());
        $remove_days = ($weeks * 2); // remove weekends
        if (config('continuum.closed_bank_holidays', true)) {
            $remove_days += $this->getBankHolidays()->count();
        }
        return $this->getStart()->copy()->diffInDays($this->getEnd()) - $remove_days;
    }

    public function countWeeks() : int
    {
        return (int) $this->start->copy()->diffInWeeks($this->end);
    }

    public function setHalfTerm()
    {
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
}
