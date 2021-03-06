<?php

namespace Loopy\Continuum\Classes\Academic;

use Loopy\Continuum\Services\Continuum;
use Carbon\Carbon;
use \Illuminate\Support\Collection;
use JsonSerializable;

abstract class Term implements JsonSerializable
{
    protected $start;
    protected $end;
    protected $days;
    protected $day_count = 0;
    protected $week_count = 0;
    protected $month_count = 0;
    protected $name = '';
    protected $human_weeks = '';
    protected $months = [];
    protected $weeks = [];
    protected $bank_holidays = [];
    protected $closed_dates = [];
    protected $half_term_active = true;

    public function __construct(Carbon $start_date, Carbon $end_date)
    {
        $this->provider = new Continuum;
        $this->setStart($start_date->startOfDay());
        $this->setEnd($end_date->endOfDay());
        $this->setTermDates();
        $this->setWeeks();
        $this->setMonths();
        $this->day_count = $this->countDaysInTerm();
        $this->week_count = $this->countWeeks(false);
        $this->setMonthCount(empty($this->month_count) ? count($this->getMonths()) : $this->month_count);
        $this->setHumanWeeks();
    }

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
        if ($half_term) {
            $this->setHalfTerm();
        }
        return $this;
    }

    public function setStart(Carbon $start) : Term
    {
        $this->start = $start;
        return $this;
    }

    public function setEnd(Carbon $end) : Term
    {
        $this->end = $end;
        return $this;
    }

    public function setName(string $name) : Term
    {
        $this->name = $name;
        return $this;
    }

    public function setMonthCount(int $count) : Term
    {
        $this->month_count = $count;
        return $this;
    }

    public function setBankHoliday(Carbon $bank_holiday) : Term
    {
        $this->bank_holidays = array_merge($this->bank_holidays, [$bank_holiday]);
        $this->bank_holidays = $this->bank_holidays;
        return $this;
    }

    public function setClosedDates(array $closed_dates) : Term
    {
        $this->closed_dates = [];

        foreach ($closed_dates as $date) {
            $this->closed_dates[] = Carbon::createFromFormat('Y-m-d', $date);
        }
        return $this;
    }

    public function setWeeks()
    {
        $weeks = $this->provider->compare()->getWeeksBetween($this->getStart(), $this->getEnd());
        foreach ($weeks as $week) {
            $this->weeks[] = $week->startOfWeek();
        }
        $this->weeks = collect($this->getWeeks());
    }

    public function setMonths()
    {
        $months = $this->provider->compare()->getMonthsBetween($this->getStart(), $this->getEnd());
        foreach ($months as $month) {
            $this->months[$month->month] = $month->format('F');
        }
        $this->months = collect($this->getMonths());
    }

    public function setTermDates()
    {
        $date_range = $this->provider->compare()->getDaysBetween($this->getStart(), $this->getEnd());
        $holiday_provider = $this->provider->getBankHolidayProvider($this->getStart()->year, 2);

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

    public function toArray() : array
    {
        $result = [];
        if (!empty($this->half_term)) {
            $result = [
                'half_term' => $this->half_term,
                'half_term_bank_holiday' => $this->half_term_bank_holiday,
            ];
        }
        return array_merge($result, [
            'name' => $this->getName(),
            'start' => $this->getStart(),
            'end' => $this->getEnd(),
            'days' => $this->getDays(),
            'weeks' => $this->getWeeks(),
            'months' => $this->getMonths(),
            'bank_holidays' => $this->getBankHolidays(),
            'day_count' => $this->getDayCount(),
            'week_count' => $this->getWeekCount(),
            'month_count' => $this->getMonthCount(),
            'human_weeks' => $this->getHumanWeeks(),
            'half_term_active' => $this->half_term_active,
            'closed_dates' => $this->getClosedDates()
        ]);
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    abstract protected function countDaysInTerm();
    abstract protected function countWeeks();
    abstract protected function setHalfTerm();

    private function setHumanWeeks()
    {
        $this->human_weeks = floor($this->day_count / 5) . ' weeks and ' . $this->day_count % 5 . ' days';
    }
}
