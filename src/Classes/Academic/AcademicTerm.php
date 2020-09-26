<?php

namespace Loopy\Continuum\Classes\Academic;

use Continuum;
use Carbon\Carbon;
use \Illuminate\Support\Collection;
use JsonSerializable;

class AcademicTerm implements JsonSerializable
{
    protected $name;
    protected $start;
    protected $end;
    protected $days;
    protected $weeks;
    protected $months;
    protected $half_term;
    protected $bank_holidays = [];
    protected $half_term_bank_holiday;
    protected $day_count;
    protected $week_count;
    protected $month_count;
    protected $day_difference;
    protected $human_weeks;
    protected $half_term_active = true;
    protected $closed_dates = [];

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

    public function getBankHolidays() :  Collection
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

    public function getDayDifference() : int
    {
        return $this->day_difference;
    }

    public function getHumanWeeks() : string
    {
        return $this->human_weeks;
    }

    public function getMonthCount() : int
    {
        return $this->month_count;
    }

    public function getClosedDates() : array
    {
        return $this->closed_dates;
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
        $this->setTermDates();
        $this->setWeeks();
        $this->setMonths();
        $this->day_count = $this->countDaysInTerm();
        $this->day_difference = (int) $this->getTotalTermDayDiff();
        $this->week_count = (int) $this->countWeeks();
        $this->month_count = (empty($this->month_count) ? count($this->getMonths()) : $this->month_count);
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

    private function setHalfTerm()
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

    private function getTotalTermDayDiff() : string
    {
        $dif_start = $this->getStart()->copy()->diffInDays($this->getStart()->copy()->startOfWeek());
        $dif_end = $this->getEnd()->copy()->diffInDays($this->getEnd()->copy()->endOfWeek()) - 2;
        $days = $dif_end + $dif_start;
        return $days;
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

    private function countWeeks()
    {
        /* Remove Half Term */
        return $this->getStart()->copy()->diffInWeeks($this->getEnd()) - 1;
    }

    private function countDaysInTerm()
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

    private function setTermDates()
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
}
