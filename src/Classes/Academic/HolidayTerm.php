<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;

class HolidayTerm extends Term
{
    public function __construct(Carbon $start_date, Carbon $end_date)
    {
        parent::__construct($start_date, $end_date);
        $this->halfTermActive(false);
    }

    public function countDaysInTerm()
    {
        $weeks = $this->getStart()->copy()->diffInWeeks($this->getEnd());
        $remove_days = ($weeks * 2); // remove weekends
        return $this->getStart()->copy()->diffInDays($this->getEnd()) - $remove_days;
    }

    public function countWeeks(bool $exact = true) : int
    {
        if ($exact) {
            return (int) $this->getStart()->copy()->diffInWeeks($this->getEnd());
        }

        return (int) ceil($this->day_count / 5);
    }

    public function setHalfTerm()
    {
        //
    }
}
