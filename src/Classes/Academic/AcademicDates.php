<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use Loopy\Continuum\Services\Continuum;

class AcademicDates
{
    protected $end_year;
    protected $start_year;

    public function setStartYear(int $start_year) : AcademicDates
    {
        $this->start_year = $start_year;
        $this->end_year = $this->start_year + 1;
        return $this;
    }

    public function getStartYear() : int
    {
        return $this->start_year;
    }

    public function getEndYear() : int
    {
        return $this->end_year;
    }

    public function getFirstDayOfSummerTerm() : Carbon
    {
        return $this->getLastDayOfSpringTerm()->copy()->addWeeks(3)->startOfWeek();
    }

    public function getLastDayOfSummerTerm() : Carbon
    {
        return Carbon::parse('last friday of july ' . $this->end_year)->subWeek();
    }

    public function getFirstDayOfAutumnTerm() : Carbon
    {
        return Carbon::parse('first monday of september ' . $this->start_year);
    }

    public function getLastDayOfAutumnTerm() : Carbon
    {
        return $this->getFirstDayOfSpringTerm()->copy()->endOfWeek()->subWeeks(3)->subDays(2)->startOfDay();
    }

    public function getFirstDayOfSummerHolidays() : Carbon
    {
        return $this->getLastDayOfSummerTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    public function getLastDayOfSummerHolidays() : Carbon
    {
        return Carbon::parse('first monday of september ' . $this->end_year)->subWeeks(1)->endOfWeek()->subDays(2)->startOfDay();
    }

    public function getFirstDayOfEasterHolidays() : Carbon
    {
        return $this->getLastDayOfSpringTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    public function getLastDayOfEasterHolidays() : Carbon
    {
        return $this->getFirstDayOfSummerTerm()->copy()->subWeeks(1)->endOfWeek()->subDays(2)->startOfDay();
    }

    public function getFirstDayOfChristmasHolidays() : Carbon
    {
        return $this->getLastDayOfAutumnTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    public function getLastDayOfChristmasHolidays() : Carbon
    {
        return $this->getFirstDayOfSpringTerm()->copy()->subWeeks(1)->endOfWeek()->subDays(2)->startOfDay();
    }

    public function getFirstDayOfSpringTerm() : Carbon
    {
        $provider = new Continuum;
        $holiday_provider = $provider->getBankHolidayProvider($this->end_year);
        $new_years_day = $holiday_provider->getNewYearsDay();

        if ($new_years_day->isWeekday()) {
            if ($new_years_day->isMonday()) {
                return $new_years_day->copy()->addDays(1)->startOfDay();
            }
            return $new_years_day->copy()->addWeek()->startOfWeek()->startOfDay();
        }
        return $new_years_day->copy()->addWeek()->startOfWeek()->addDays(1)->startOfDay();
    }

    public function getLastDayOfSpringTerm() : Carbon
    {
        $provider = new Continuum;
        $holiday_provider = $provider->getBankHolidayProvider($this->end_year);
        $easter_mid = Carbon::parse('26th march ' . $this->end_year)->addDays(13);

        if ($holiday_provider->getEasterSaturday()->lte($easter_mid)) {
            return $holiday_provider->getGoodFriday()->copy()->subDays(1)->startOfDay();
        } else {
            return $holiday_provider->getEasterSaturday()->copy()->subDay(1)->subWeeks(2)->startOfDay();
        }
    }
}
