<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use \Illuminate\Support\Collection;
use JsonSerializable;

abstract class Year extends AcademicDates implements JsonSerializable
{
    protected $start_year;
    protected $end_year;
    protected $terms;
    protected $over_days;
    protected $weeks;
    protected $days;
    protected $closed_dates = [];
    protected $holidays = [];

    const TOTAL_DAYS = 190;
    const TOTAL_WEEKS = 38;

    public function __construct(int $start_year)
    {
        $this->closed_dates = collect([]);
        $this->setStartYear($start_year);
        $this->createAcademicTerms();
        $this->getNonTermTime();
        $this->over_days = $this->days - self::TOTAL_DAYS;
    }

    public function getTerms() : Collection
    {
        return collect($this->terms);
    }

    public function getHolidays() : Collection
    {
        return collect($this->holidays);
    }

    public function getAutumnTerm() : Term
    {
        return $this->terms['Autumn'];
    }

    public function getSpringTerm() : Term
    {
        return $this->terms['Spring'];
    }

    public function getSummerTerm() : Term
    {
        return $this->terms['Summer'];
    }

    public function getChristmasHolidays() : Term
    {
        return $this->holidays['Christmas'];
    }

    public function getEasterHolidays() : Term
    {
        return $this->holidays['Easter'];
    }

    public function getSummerHolidays() : Term
    {
        return $this->holidays['Summer'];
    }

    public function getCurrentTermName() : string
    {
        $term_name = $this->getCurrentTerm(true);
        if ($term_name == 'Easter') {
            $term_name = 'Spring';
        } elseif ($term_name == 'Christmas') {
            $term_name = 'Autumn';
        }
        return $term_name;
    }

    public function getPreviousTermName($term_name = null) : string
    {
        if (is_null($term_name)) {
            $term_name = $this->getCurrentTerm(true);
        }
        switch (strtolower($term_name)) {
            case "easter":
            case "spring":
                $term_name = 'Autumn';
                break;
            case "summer":
                $term_name = 'Spring';
                break;
            case "christmas":
            case "autumn":
                $term_name = 'Summer';
                break;
        }
        return $term_name;
    }

    public function getNextTermName($term_name = null) : string
    {
        if (is_null($term_name)) {
            $term_name = $this->getCurrentTerm(true);
        }
        switch (strtolower($term_name)) {
            case "easter":
            case "spring":
                $term_name = 'Summer';
                break;
            case "summer":
                $term_name = 'Autumn';
                break;
            case "christmas":
            case "autumn":
                $term_name = 'Spring';
                break;
        }
        return $term_name;
    }

    public function getCurrentTerm(bool $name_only = false)
    {
        $current_term = $this->getTerms()->filter(function ($item) {
            return Carbon::now()->gte($item->getStart()) && Carbon::now()->lte($item->getEnd());
        })->first();
        if ($current_term) {
            return $name_only ? $current_term->getName() : $current_term;
        }

        $current_holiday = $this->getHolidays()->filter(function ($item) {
            return Carbon::now()->gte($item->getStart()) && Carbon::now()->lte($item->getEnd());
        })->first();
        if ($current_holiday) {
            return $name_only ? $current_holiday->getName() : $current_holiday;
        }

        return $name_only ? 'Autumn' : $this->getAutumnTerm();
    }

    public function getNextTerm() : Term
    {
        $method = 'get' . $this->getNextTermName() . 'Term';
        return $this->$method();
    }

    public function getPreviousTerm(string $term_name = null) : Term
    {
        $method = 'get' . $this->getPreviousTermName($term_name) . 'Term';
        return $this->$method();
    }

    public function countAllTermWeeks() : int
    {
        return $this->weeks;
    }

    public function countAllWeeksInSpring() : int
    {
        return $this->getSpringTerm()->getWeekCount() + $this->getEasterHolidays()->getWeekCount();
    }

    public function countAllWeeksInAutumn() : int
    {
        return $this->getAutumnTerm()->getWeekCount() + $this->getChristmasHolidays()->getWeekCount();
    }

    public function countAllWeeksInSummer() : int
    {
        return $this->getSummerTerm()->getWeekCount() + $this->getSummerHolidays()->getWeekCount();
    }

    public function term(string $term) : Term
    {
        switch ($term) {
            case "autumn":
                return $this->getAutumnTerm();
            case "spring":
                return $this->getSpringTerm();
            case "summer":
                return $this->getSummerTerm();
        }
        throw new Exception('Unable to find term.');
    }

    public function toArray() : array
    {
        return [
            'start_year' => $this->getStartYear(),
            'end_year' => $this->getEndYear(),
            'terms' => $this->getTerms(),
            'days' => $this->days,
            'weeks' => $this->weeks,
            'holidays' => $this->getHolidays(),
            'over_days' => $this->over_days,
            'closed_dates' => $this->closed_dates
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    private function setAutumnTerm(Term $term) : Year
    {
        $this->terms['Autumn'] = $term;
        return $this;
    }

    private function setSummerTerm(Term $term) : Year
    {
        $this->terms['Summer'] = $term;
        return $this;
    }

    private function setSpringTerm(Term $term) : Year
    {
        $this->terms['Spring'] = $term;
        return $this;
    }

    private function createAcademicTerms()
    {
        $this->setAutumnTerm($this->buildAutumnTerm());
        $this->setSpringTerm($this->buildSpringTerm());
        $this->setSummerTerm($this->buildSummerTerm());
        $this->weeks = $this->terms['Autumn']->getWeekCount() + $this->terms['Spring']->getWeekCount() + $this->terms['Summer']->getWeekCount() - 3;
        $this->days = $this->terms['Autumn']->getDayCount() + $this->terms['Spring']->getDayCount() + $this->terms['Summer']->getDayCount() - 15;
    }

    private function getNonTermTime()
    {
        $this->holidays['Christmas'] = $this->buildChristmasHolidays();
        $this->holidays['Easter'] = $this->buildEasterHolidays();
        $this->holidays['Summer'] = $this->buildSummerHolidays();
    }

    private function buildChristmasHolidays() : Term
    {
        return (new HolidayTerm($this->getFirstDayOfChristmasHolidays(), $this->getLastDayOfChristmasHolidays()))
        ->setName('Christmas');
    }

    private function buildEasterHolidays() : Term
    {
        return (new HolidayTerm($this->getFirstDayOfEasterHolidays(), $this->getLastDayOfEasterHolidays()))
        ->setName('Easter');
    }

    private function buildSummerHolidays() : Term
    {
        return (new HolidayTerm($this->getFirstDayOfSummerHolidays(), $this->getLastDayOfSummerHolidays()))
        ->setName('Summer');
    }

    abstract protected function buildAutumnTerm();
    abstract protected function buildSpringTerm();
    abstract protected function buildSummerTerm();
}
