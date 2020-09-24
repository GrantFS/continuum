<?php

namespace Loopy\Continuum\Classes;

use Carbon\Carbon;

class BankHolidayProvider
{
    protected $year;
    protected $bank_holidays;
    protected $next_year;

    public function __construct(string $year, int $number_of_years = 1)
    {
        $this->year = $year;
        $this->bank_holidays = $this->getBankHolidays(false);
        $this->addExtraYears($number_of_years);
    }

    public function isBankHoliday($date) : bool
    {
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }
        return in_array($date, $this->bank_holidays);
    }

    public function getBankHolidays(bool $carbon = true)
    {
        $bank_holidays = ['25th Dec', '26 Dec'];

        foreach ($bank_holidays as $bank_holiday) {
            $dates[] = Carbon::parse($bank_holiday . ' ' . $this->year);
        }
        $dates = array_merge($dates, [$this->getNewYearsDay()]);
        $dates = array_merge($dates, $this->getMayBankHolidays());
        $dates = array_merge($dates, $this->getAugustBankHoliday());
        $dates = array_merge($dates, $this->getEasterBankHolidays());
        if ($carbon) {
            return collect($dates);
        }
        return $this->convertToDates($dates);
    }

    public function getNewYearsDay() : Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->year . '-01-01');
    }

    public function getMayBankHolidays() : array
    {
        $dates[] = Carbon::parse('first monday of may ' . $this->year);
        $dates[] = Carbon::parse('last monday of may ' . $this->year);
        return $dates;
    }

    public function getAugustBankHoliday() : array
    {
        $dates[] = Carbon::parse('last monday of august ' . $this->year);
        return $dates;
    }

    public function getEasterBankHolidays() : array
    {
        $dates[] = $this->getGoodFriday();
        $dates[] = $this->getEasterMonday();
        return $dates;
    }

    public function getEasterMonday() : Carbon
    {
        return $this->getEasterSaturday()->AddDays(2);
    }

    public function getGoodFriday() : Carbon
    {
        return $this->getEasterSaturday()->subDays(1);
    }

    public function getEasterSaturday() : Carbon
    {
        return Carbon::createFromFormat('Y-m-d', date("Y-m-d", easter_date($this->year)));
    }

    private function convertToDates(array $dates) : array
    {
        $dates_array = [];
        foreach ($dates as $date) {
            $dates_array[] = $date->format('Y-m-d');
        }
        return $dates_array;
    }

    private function addExtraYears(int $number_of_years)
    {
        while ($number_of_years > 1) {
            $number_of_years --;

            $next_year =   new self($this->year +1, $number_of_years);
            $this->bank_holidays = array_merge($this->bank_holidays, $next_year->getBankHolidays(false));
        }
    }
}
