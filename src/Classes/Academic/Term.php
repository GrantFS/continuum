<?php

namespace Loopy\Continuum\Classes\Academic;

use Continuum;
use Carbon\Carbon;
use \Illuminate\Support\Collection;

abstract class Term
{
    protected $start;
    protected $end;
    protected $name;
    protected $stretched;
    protected $days;
    protected $day_count = 0;
    protected $week_count = 0;
    protected $month_count = 0;
    protected $day_difference = 0;
    protected $human_weeks = '';
    protected $months = [];
    protected $weeks = [];
    protected $bank_holidays = [];
    protected $closed_dates = [];
    protected $half_term_active = true;


    public function getStart() : Carbon
    {
        return $this->start;
    }

    public function getEnd() : Carbon
    {
        return $this->end;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getHumanWeeks() : string
    {
        return $this->human_weeks;
    }

    public function getStretched()
    {
        return $this->stretched;
    }

    public function getStretechedEnd() : Carbon
    {
        return $this->getStretched()->getEnd();
    }

    public function getDays() :  Collection
    {
        return is_array($this->days) ? collect($this->days) : $this->days;
    }

    public function getWeeks() :  Collection
    {
        return is_array($this->weeks) ? collect($this->weeks) : $this->weeks;
    }

    public function getMonths() :  Collection
    {
        return is_array($this->months) ? collect($this->months) : $this->months;
    }

    public function getDayCount() : int
    {
        return $this->day_count;
    }

    public function getWeekCount() : int
    {
        return $this->week_count;
    }

    public function getMonthCount() : int
    {
        return $this->month_count;
    }

    public function getDayDifference() : int
    {
        return $this->day_difference;
    }

    public function getBankHolidays() :  Collection
    {
        return collect($this->bank_holidays);
    }

    public function getClosedDates() : array
    {
        return $this->closed_dates;
    }

    public function halfTermActive(bool $half_term = true) : Term
    {
        $this->half_term_active = $half_term;
        return $this;
    }

    public function setStart(Carbon $start) : Term
    {
        $this->start = $start;
        if (!empty($this->end)) {
            $this->process();
        }
        return $this;
    }

    public function setEnd(Carbon $end) : Term
    {
        $this->end = $end;
        if (!empty($this->start)) {
            $this->process();
        }
        return $this;
    }

    public function setName(string $name) : Term
    {
        $this->name = $name;
        return $this;
    }

    private function setWeeks()
    {
        $weeks = Continuum::compare()->getWeeksBetween($this->getStart(), $this->getEnd());
        foreach ($weeks as $week) {
            $this->weeks[] = $week->startOfWeek();
        }
        $this->weeks = collect($this->getWeeks());
    }

    private function setMonths()
    {
        $months = Continuum::compare()->getMonthsBetween($this->getStart(), $this->getEnd());
        foreach ($months as $month) {
            $this->months[$month->month] = $month->format('F');
        }
        $this->months = collect($this->getMonths());
    }

    public function process() : AcademicTerm
    {
        $this->setTermDates();
        $this->setWeeks();
        $this->setMonths();
        $this->day_count = $this->countDaysInTerm();
        $this->day_difference = $this->getTotalTermDayDiff();
        $this->week_count = (int) $this->countWeeks();
        $this->month_count = (empty($this->month_count) ? count($this->getMonths()) : $this->month_count);
        $this->human_weeks =  $this->week_count . ' weeks and ' . $this->day_difference . ' days';
        if ($this->half_term_active) {
            $this->setHalfTerm();
        }
        return $this;
    }

    public function setTermDates()
    {
        $date_range = Continuum::compare()->getDaysBetween($this->getStart(), $this->getEnd());
        $holiday_provider = Continuum::getBankHolidayProvider($this->getStart()->year, 2);

        foreach ($date_range as $value) {
            if (!$value->isSaturday() && !$value->isSunday()) {
                if ($holiday_provider->isBankHoliday($value)) {
                    $this->setBankHoliday($value);
                } else {
                    $this->days[$value->format('Y-m-d')] = $value->dayOfWeek;
                }
            }
        }
        $this->days = collect($this->getDays());
    }

    public function getTotalTermDayDiff() : int
    {
        $dif_start = $this->getStart()->copy()->diffInDays($this->getStart()->copy()->startOfWeek());
        $dif_end = $this->getEnd()->copy()->diffInDays($this->getEnd()->copy()->endOfWeek()) - 2;
        $days = $dif_end + $dif_start;
        return (int) $days;
    }

    abstract protected function setBankHoliday(Carbon $bank_holiday);
    abstract protected function countDaysInTerm();
    abstract protected function countWeeks();
    abstract protected function setHalfTerm();
}

