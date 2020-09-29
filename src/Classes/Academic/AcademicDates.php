<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use Continuum;

class AcademicDates
{
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
        return $this->getFirstDayOfSpringTerm()->copy()->startOfWeek()->subWeeks(3)->endOfWeek()->subDays(2);
    }

    public function getFirstDayOfSummerHolidays() : Carbon
    {
        return $this->getLastDayOfSummerTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    public function getLastDayOfSummerHolidays() : Carbon
    {
        return Carbon::parse('first monday of september ' . $this->end_year)->subWeeks(1)->endOfWeek()->subDays(2);
    }

    public function getFirstDayOfEasterHolidays() : Carbon
    {
        return $this->getLastDayOfSpringTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    public function getLastDayOfEasterHolidays() : Carbon
    {
        return $this->getFirstDayOfSummerTerm()->copy()->subWeeks(1)->endOfWeek()->subDays(2);
    }

    public function getFirstDayOfChristmasHolidays() : Carbon
    {
        return $this->getLastDayOfAutumnTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    public function getLastDayOfChristmasHolidays() : Carbon
    {
        return $this->getFirstDayOfSpringTerm()->copy()->subWeeks(1)->endOfWeek()->subDays(2);
    }

    public function getFirstDayOfSpringTerm() : Carbon
    {
        $holiday_provider = Continuum::getBankHolidayProvider($this->end_year);
        $new_years_day = $holiday_provider->getNewYearsDay();

        if ($new_years_day->isWeekday()) {
            if ($new_years_day->isMonday()) {
                return $new_years_day->copy()->addDays(1);
            }
            return $new_years_day->copy()->addWeek()->startOfWeek();
        }
        return $new_years_day->copy()->addWeek()->startOfWeek()->addDays(1);
    }

    public function getLastDayOfSpringTerm() : Carbon
    {
        $holiday_provider = Continuum::getBankHolidayProvider($this->end_year);
        $easter_mid = Carbon::parse('26th march ' . $this->end_year)->addDays(13);

        if ($holiday_provider->getEasterSaturday()->lte($easter_mid)) {
            return $holiday_provider->getGoodFriday()->copy()->subDays(1);
        } else {
            return $holiday_provider->getEasterSaturday()->copy()->subDay(1)->subWeeks(2);
        }
    }
}
