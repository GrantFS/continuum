<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;

class AcademicYear extends Year
{
    protected $closed_dates;

    public function buildAutumnTerm() : AcademicTerm
    {
        return (new AcademicTerm($this->getFirstDayOfAutumnTerm(), $this->getLastDayOfAutumnTerm()))
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfAutumnTerm(), $this->getLastDayOfAutumnTerm()))
        ->setName('Autumn');
    }

    public function buildSpringTerm() : AcademicTerm
    {
        return (new AcademicTerm($this->getFirstDayOfSpringTerm(), $this->getLastDayOfSpringTerm()))
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfSpringTerm(), $this->getLastDayOfEasterHolidays()))
        ->setName('Spring');
    }

    public function buildSummerTerm() : AcademicTerm
    {
        return (new AcademicTerm($this->getFirstDayOfSummerTerm(), $this->getLastDayOfSummerTerm()))
        ->setMonthCount(5)
        ->setName('Summer')
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfSummerTerm(), $this->getLastDayOfSummerHolidays()));
    }

    private function getClosedDates(Carbon $start_date, Carbon $end_date) : array
    {
        return $this->closed_dates->filter(function ($item) use ($start_date, $end_date) {
            $date = Carbon::createFromFormat('Y-m-d', $item);
            return $start_date->lte($date) && $end_date->gte($date);
        })->toArray();
    }
}
