<?php

namespace Loopy\Continuum\Classes\Academic;

use Loopy\Continuum\Services\Continuum;
use Carbon\Carbon;
use \Illuminate\Support\Collection;

class AcademicTerm extends Term
{
    protected $half_term;
    protected $half_term_bank_holiday;

    public function __construct(Carbon $start_date, Carbon $end_date)
    {
        parent::__construct($start_date, $end_date);
        $this->setHalfTerm();
    }

    public function countDaysInTerm()
    {
        $weeks = $this->getStart()->copy()->diffInWeeks($this->getEnd());
        $remove_days = ($weeks * 2); // remove weekends
        $remove_days += 5; // remove half term
        if (config('continuum.closed_bank_holidays', true)) {
            $remove_days += $this->getBankHolidays()->count();
        }
        return $this->getStart()->copy()->diffInDays($this->getEnd()) - $remove_days;
    }

    public function countWeeks() : int
    {
        /* Remove Half Term */
        return (int) $this->getStart()->copy()->diffInWeeks($this->getEnd()) - 1;
    }

    public function setHalfTerm()
    {
        if ($this->getStart()->month == 4) {
            $this->half_term = Carbon::parse("last monday of may " . $this->getStart()->format('Y'));
        } elseif ($this->getStart()->month == 9) {
            $oct = Carbon::parse("last friday of october " . $this->getStart()->format('Y'));
            $this->half_term = $oct->startOfWeek();
        } else {
            $weeks = $this->week_count / 2;
            $weeks = number_format(round($weeks, 0, PHP_ROUND_HALF_UP), 0);

            $this->half_term = $this->weeks[$weeks];
            if ($weeks % 2 == 0) {
                $this->half_term = $this->weeks[$weeks];
            } else {
                $this->half_term = $this->weeks[$weeks - 1];
            }
        }
        $this->removeHalfTermFromDays();
        $this->removeHalfTermFromWeeks();
        $this->moveHalfTermBankHolidays();
    }

    public function getHalfTermBankHolidays() : Collection
    {
        return collect($this->half_term_bank_holiday);
    }

    public function getHalfTerm() : Carbon
    {
        return $this->half_term;
    }

    public function getHalfTermEnd() : Carbon
    {
        $end = clone $this->half_term;
        return $end->addDays(7);
    }

    public function isHalfTerm(string $date) : bool
    {
        $dates = $this->getHalfTermDates();
        foreach ($dates as $day) {
            if ($day->format('Y-m-d') == $date) {
                return true;
            }
        }
        return false;
    }

    public function getHalfTermDates() : array
    {
        $half_term_start = $this->getHalfTerm();
        $half_term_end = clone $half_term_start;
        $half_term_end = $half_term_end->addDays(7);
        $provider = new Continuum;
        return $provider->compare()->getDatesBetween($half_term_start->format('Y-m-d'), $half_term_end->format('Y-m-d'));
    }

    public function getWeekCountWithoutHolidays() : int
    {
        /* Remove half term */
        $count = $this->week_count -1;
        $days = 0;
        /* Remove bank holidays */
        if (config('continuum.closed_bank_holidays', true)) {
            if (count($this->bank_holidays) > 0) {
                $days = $days - (count($this->bank_holidays) / 5);
            }
        }
        /* Remove Holidays */
        foreach ($this->closed_dates as $date) {
            $days = $date->isWeekend() || $this->isHalfTerm($date->format('Y-m-d')) ? $days : $days + 1;
        }
        $count = $count - ($days / 5);
        return ceil($count);
    }

    private function moveHalfTermBankHolidays()
    {
        $half_term_start = $this->getHalfTerm()->copy();
        $half_term_end = $this->getHalfTerm()->copy()->addDays(6);
        foreach ($this->bank_holidays as $id => $bank_holiday) {
            if ($bank_holiday->gte($half_term_start) && $bank_holiday->lte($half_term_end)) {
                $this->half_term_bank_holiday[] = $bank_holiday;
                unset($this->bank_holidays[$id]);
            }
        }
    }

    private function removeHalfTermFromDays()
    {
        foreach ($this->getDays() as $date => $day) {
            if ($this->half_term->format('Y-m-d') == $date) {
                unset($this->days[$date]);
            }
        }
    }

    private function removeHalfTermFromWeeks()
    {
        foreach ($this->getWeeks() as $id => $week) {
            if ($this->half_term->format('Y-m-d') == $week->format('Y-m-d')) {
                unset($this->weeks[$id]);
            }
        }
    }
}
