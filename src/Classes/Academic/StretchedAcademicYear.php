<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;

class StretchedAcademicYear extends Year
{
    protected $closed_dates;

    public function buildAutumnTerm() : Term
    {
        return (new StretchedTerm($this->getFirstDayOfAutumnTerm(), $this->getLastDayOfChristmasHolidays()))
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfAutumnTerm(), $this->getLastDayOfAutumnTerm()))
        ->setName('Autumn');
    }

    public function buildSpringTerm() : Term
    {
        return (new StretchedTerm($this->getFirstDayOfSpringTerm(), $this->getLastDayOfEasterHolidays()))
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfSpringTerm(), $this->getLastDayOfEasterHolidays()))
        ->setName('Spring');
    }

    public function buildSummerTerm() : Term
    {
        return (new StretchedTerm($this->getFirstDayOfSummerTerm(), $this->getLastDayOfSummerHolidays()))
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfSummerTerm(), $this->getLastDayOfSummerHolidays()))
        ->setMonthCount(5)
        ->setName('Summer');
    }

    private function getClosedDates(Carbon $start_date, Carbon $end_date) : array
    {
        return $this->closed_dates->filter(function ($item) use ($start_date, $end_date) {
            $date = Carbon::createFromFormat('Y-m-d', $item);
            return $start_date->lte($date) && $end_date->gte($date);
        })->toArray();
    }
}
