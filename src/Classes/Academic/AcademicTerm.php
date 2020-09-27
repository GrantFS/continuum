<?php

namespace Loopy\Continuum\Classes\Academic;

use Continuum;
use Carbon\Carbon;
use JsonSerializable;

class AcademicTerm extends Term implements JsonSerializable
{
    protected $half_term;
    protected $half_term_bank_holiday;

    public function __construct(Carbon $start_date, Carbon $end_date)
    {
        $this->start = $start_date;
        $this->end = $end_date;
        $this->setTermDates();
        $this->day_difference = $this->getTotalTermDayDiff();
        $this->day_count = $this->countDaysInTerm();
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
        return Continuum::compare()->getDatesBetween($half_term_start->format('Y-m-d'), $half_term_end->format('Y-m-d'));
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

    public function setStretched(StretchedTerm $stretched) : AcademicTerm
    {
        $this->stretched = $stretched;

        return $this;
    }

    public function setBankHoliday(Carbon $bank_holiday) : AcademicTerm
    {
        $this->bank_holidays = array_merge($this->bank_holidays, [$bank_holiday]);
        $this->bank_holidays = $this->bank_holidays;
        return $this;
    }

    public function setMonthCount(int $count) : AcademicTerm
    {
        $this->month_count = $count;
        return $this;
    }

    public function setClosedDates(array $closed_dates) : AcademicTerm
    {
        $this->closed_dates = [];

        foreach ($closed_dates as $date) {
            $this->closed_dates[] = Carbon::createFromFormat('Y-m-d', $date);
        }
        if (!empty($this->stretched)) {
            $this->stretched->setClosedDates($this->closed_dates);
        }
        return $this;
    }

    public function toArray() : array
    {
        return [
            'name' => $this->getName(),
            'start' => $this->getStart(),
            'end' => $this->getEnd(),
            'days' => $this->getDays(),
            'weeks' => $this->getWeeks(),
            'months' => $this->getMonths(),
            'half_term' => $this->half_term,
            'bank_holidays' => $this->getBankHolidays(),
            'half_term_bank_holiday' => $this->half_term_bank_holiday,
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

    public function countWeeks()
    {
        /* Remove Half Term */
        return $this->getStart()->copy()->diffInWeeks($this->getEnd()) - 1;
    }

    public function countDaysInTerm()
    {
        $weeks = $this->getStart()->copy()->diffInWeeks($this->getEnd());
        return $this->getStart()->copy()->diffInDays($this->getEnd()) - ($weeks * 2); // removes weekends
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
