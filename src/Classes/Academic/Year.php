<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use JsonSerializable;

abstract class Year extends AcademicDates implements JsonSerializable
{
    protected $start_year;
    protected $end_year;
    protected $terms;
    protected $over_days;
    protected $weeks;
    protected $days;
    protected $holidays = [];

    const TOTAL_DAYS = 190;
    const TOTAL_WEEKS = 38;

    public function __construct(int $start_year)
    {
        $this->closed_dates = collect([]);
        $this->start_year = $start_year;
        $this->end_year = $this->start_year + 1;
        $this->createAcademicTerms();
        $this->getNonTermTime();
        $this->over_days = $this->days - self::TOTAL_DAYS;
    }

    public function getStartYear() : int
    {
        return $this->start_year;
    }

    public function getEndYear() : int
    {
        return $this->end_year;
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

    public function createAcademicTerms()
    {
        $this->setAutumnTerm($this->buildAutumnTerm());
        $this->setSpringTerm($this->buildSpringTerm());
        $this->setSummerTerm($this->buildSummerTerm());
        $this->weeks = $this->terms['Autumn']->getWeekCount() + $this->terms['Spring']->getWeekCount() + $this->terms['Summer']->getWeekCount() - 3;
        $this->days = $this->terms['Autumn']->getDayCount() + $this->terms['Spring']->getDayCount() + $this->terms['Summer']->getDayCount() - 15;
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
    // ?
    public function getCurrentTerm(bool $name_only = false)
    {
        foreach ($this->terms as $name => $term) {
            if (now()->gte($term->getStart()) && now()->lte($term->getEnd())) {
                if ($name_only) {
                    return $name;
                }
                return $term;
            }
        }
        foreach ($this->holidays as $name => $holiday) {
            if (now()->gte($holiday->getStart()) && now()->lte($holiday->getEnd())) {
                if ($name_only) {
                    return $name;
                }
                return $term;
            }
        }
        if ($name_only) {
            return 'Autumn';
        }
        return $this->getAutumnTerm();
    }

    public function getNextTerm() : AcademicTerm
    {
        $method = 'get' . $this->getNextTermName() . 'Term';
        return $this->$method();
    }

    public function getPreviousTerm(string $term_name = null) : AcademicTerm
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

    public function term(string $term) : AcademicTerm
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
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'terms' => $this->terms,
            'days' => $this->days,
            'weeks' => $this->weeks,
            'holidays' => $this->holidays,
            'over_days' => $this->over_days,
            'closed_dates' => $this->closed_dates
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    private function getNonTermTime()
    {
        $this->holidays['Christmas'] = $this->buildChristmasHolidays();
        $this->holidays['Easter'] = $this->buildEasterHolidays();
        $this->holidays['Summer'] = $this->buildSummerHolidays();
    }

    private function buildChristmasHolidays() : AcademicTerm
    {
        $term = new AcademicTerm($this->getFirstDayOfChristmasHolidays(), $this->getLastDayOfChristmasHolidays());

        $term
        ->halfTermActive(false)
        ->setName('Christmas');

        return $term;
    }

    private function buildEasterHolidays() : AcademicTerm
    {
        $term = new AcademicTerm($this->getFirstDayOfEasterHolidays(), $this->getLastDayOfEasterHolidays());

        $term
        ->halfTermActive(false)
        ->setName('Easter');

        return $term;
    }

    private function buildSummerHolidays() : AcademicTerm
    {
        $term = new AcademicTerm($this->getFirstDayOfSummerHolidays(), $this->getLastDayOfSummerHolidays());
        $term
        ->halfTermActive(false)
        ->setName('Summer');

        return $term;
    }

    abstract protected function buildAutumnTerm();
    abstract protected function buildSpringTerm();
    abstract protected function buildSummerTerm();
}
