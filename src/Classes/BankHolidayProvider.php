<?php

namespace Loopy\Continuum\Classes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use JsonSerializable;

class BankHolidayProvider implements JsonSerializable
{
    protected $year;
    protected $bank_holidays;

    public function __construct(string $year, int $number_of_years = 1)
    {
        $this->year = $year;
        $this->setBankHolidays();
        $this->addExtraYears($number_of_years);
    }

    public function isBankHoliday(Carbon $date) : bool
    {
        return $this->bank_holidays->filter(function ($item) use ($date) {
            return $item->format('Y-m-d') == $date->format('Y-m-d');
        })->count() > 0;
    }

    public function get() : Collection
    {
        return $this->bank_holidays;
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

    public function getAugustBankHoliday() : Carbon
    {
        return Carbon::parse('last monday of august ' . $this->year);
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

    public function toArray() : array
    {
        $bank_holidays = $this->bank_holidays->map(function ($item) {
            return $item->format('Y-m-d');
        })->toArray();
        return [
            'year' => $this->year,
            'bank_holidays' => $bank_holidays
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    private function setBankHolidays()
    {
        $bank_holidays = ['25th Dec', '26 Dec'];

        foreach ($bank_holidays as $bank_holiday) {
            $dates[] = Carbon::parse($bank_holiday . ' ' . $this->year);
        }
        $dates = array_merge($dates, [$this->getNewYearsDay()]);
        $dates = array_merge($dates, $this->getMayBankHolidays());
        $dates = array_merge($dates, $this->getEasterBankHolidays());
        $dates = array_merge($dates, [$this->getAugustBankHoliday()]);
        $this->bank_holidays = collect($dates);
    }

    private function addExtraYears(int $number_of_years)
    {
        while ($number_of_years > 1) {
            $number_of_years --;

            $next_year = new self($this->year +1, $number_of_years);
            $this->bank_holidays = $this->bank_holidays->merge($next_year->get());
        }
    }
}
