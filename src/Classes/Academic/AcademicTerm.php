<?php

namespace Loopy\Continuum\Classes\Academic;

use Continuum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AcademicTerm
{
    protected $name;
    protected $start;
    protected $end;
    protected $days;
    protected $weeks;
    protected $months;
    protected $half_term;
    protected $bank_holidays;
    protected $half_term_bank_holiday;
    protected $day_count;
    protected $week_count;
    protected $month_count;
    protected $day_difference;
    protected $human_weeks;
    protected $half_term_active;
    protected $stretched_end;
    protected $closed_dates;

    public function __construct()
    {
        $this->bank_holidays = [];
        $this->half_term_bank_holiday = [];
        $this->half_term_active = true;
    }

    public function getStart() : Carbon
    {
        return $this->start;
    }

    public function getEnd() : Carbon
    {
        return $this->end;
    }

    public function getStretechedEnd() : Carbon
    {
        return $this->getStretched()->getEnd();
    }

    public function getDays() : Collection
    {
        return $this->days;
    }

    public function getWeeks() : Collection
    {
        return $this->weeks;
    }

    public function getMonths() : Collection
    {
        return $this->months;
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
        $half_term_start = $this->half_term;
        $half_term_end = clone $half_term_start;
        $half_term_end = $half_term_end->addDays(7);
        return Continuum::compare()->getDatesBetween($half_term_start->format('Y-m-d'), $half_term_end->format('Y-m-d'));
    }

    public function getBankHolidays() :  \Illuminate\Support\Collection
    {
        return collect($this->bank_holidays);
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getStretchedEnd() : Carbon
    {
        return $this->stretched->getEnd();
    }

    public function getStretched()
    {
        return $this->stretched;
    }

    public function getDayCount()
    {
        return $this->day_count;
    }

    public function getWeekCount()
    {
        return $this->week_count;
    }

    public function getDayDifference()
    {
        return $this->day_difference;
    }

    public function getHumanWeeks() : string
    {
        return $this->human_weeks;
    }

    public function getMonthCount()
    {
        return $this->month_count;
    }

    public function getClosedDates() : array
    {
        return $this->closed_dates;
    }

    public function getWeekCountWithoutHolidays()
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

    /* SETTERS */
    public function setStart(Carbon $start) : AcademicTerm
    {
        $this->start = $start;
        if (!empty($this->end)) {
            $this->process();
        }
        return $this;
    }

    public function setEnd(Carbon $end) : AcademicTerm
    {
        $this->end = $end;
        if (!empty($this->start)) {
            $this->process();
        }
        return $this;
    }

    public function setName(string $name) : AcademicTerm
    {
        $this->name = $name;
        return $this;
    }

    private function process() : AcademicTerm
    {
        $this->getTermDates();
        $this->setWeeks();
        $this->setMonths();
        $this->day_count = $this->countDaysInTerm();
        $this->day_difference = (int) $this->getTotalTermDayDiff();
        $this->week_count = (int) $this->countWeeks();
        $this->month_count = (empty($this->month_count) ? count($this->months) : $this->month_count);
        $this->human_weeks =  $this->week_count . ' weeks and ' . $this->day_difference . ' days';
        if ($this->half_term_active) {
            $this->setHalfTerm();
        }
        return $this;
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

    public function halfTermActive($half_term = true) : AcademicTerm
    {
        $this->half_term_active = $half_term;
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

    private function setHalfTerm()
    {
        if ($this->start->month == 4) {
            $this->half_term = Carbon::parse("last monday of may " . $this->start->format('Y'));
        } elseif ($this->start->month == 9) {
            $oct = Carbon::parse("last friday of october " . $this->start->format('Y'));
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

    private function getTotalTermDayDiff() : string
    {
        $dif_start = $this->start->copy()->diffInDays($this->start->copy()->startOfWeek());
        $dif_end = $this->end->copy()->diffInDays($this->end->copy()->endOfWeek()) - 2;
        $days = $dif_end + $dif_start;
        return $days;
    }

    private function setWeeks()
    {
        $weeks = Continuum::compare()->getWeeksBetween($this->start, $this->end);
        foreach ($weeks as $week) {
            $this->weeks[] = $week->startOfWeek();
        }
        $this->weeks = collect($this->weeks);
    }

    private function setMonths()
    {
        $months = Continuum::compare()->getMonthsBetween($this->start, $this->end);
        foreach ($months as $month) {
            $this->months[$month->month] = $month->format('F');
        }
        $this->months = collect($this->months);
    }

    private function countWeeks()
    {
        /* Remove Half Term */
        return $this->start->copy()->diffInWeeks($this->end) - 1;
    }

    private function countDaysInTerm()
    {
        $weeks = $this->start->copy()->diffInWeeks($this->end);
        return $this->start->copy()->diffInDays($this->end) - ($weeks * 2); // removes weekends
    }

    private function moveHalfTermBankHolidays()
    {
        $half_term_start = $this->half_term->copy();
        $half_term_end = $this->half_term->copy()->addDays(6);
        foreach ($this->bank_holidays as $id => $bank_holiday) {
            if ($bank_holiday->gte($half_term_start) && $bank_holiday->lte($half_term_end)) {
                $this->half_term_bank_holiday[] = $bank_holiday;
                unset($this->bank_holidays[$id]);
            }
        }
    }

    private function removeHalfTermFromDays()
    {
        foreach ($this->days as $date => $day) {
            if ($this->half_term->format('Y-m-d') == $date) {
                unset($this->days[$date]);
            }
        }
    }

    private function removeHalfTermFromWeeks()
    {
        foreach ($this->weeks as $id => $week) {
            if ($this->half_term->format('Y-m-d') == $week->format('Y-m-d')) {
                unset($this->weeks[$id]);
            }
        }
    }

    private function getTermDates()
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
