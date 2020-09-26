<?php

namespace Loopy\Continuum\Classes\Academic;

use Carbon\Carbon;
use Continuum;
use Exception;
use JsonSerializable;

class AcademicYear implements JsonSerializable
{
    protected $start_year;
    protected $end_year;
    protected $terms;
    protected $weeks;
    protected $days;
    protected $holidays;
    protected $over_days;
    protected $closed_dates;

    const TOTAL_DAYS = 190;
    const TOTAL_WEEKS = 38;

    public function __construct(int $start_year)
    {
        $this->closed_dates = collect([]);
        $this->start_year = $start_year;
        $this->end_year = $this->start_year + 1;
        $this->holidays = [];
        $this->createAcademicTerms();
        $this->getNonTermTime();
        $this->over_days = $this->days - self::TOTAL_DAYS;
    }

    /* GETTERS */

    public function getStartYear()
    {
        return $this->start_year;
    }

    public function getAutumnTerm() : AcademicTerm
    {
        return $this->terms['Autumn'];
    }

    public function getSpringTerm() : AcademicTerm
    {
        return $this->terms['Spring'];
    }

    public function getSummerTerm() : AcademicTerm
    {
        return $this->terms['Summer'];
    }

    public function getChristmasHolidays() : AcademicTerm
    {
        return $this->holidays['Christmas'];
    }

    public function getEasterHolidays() : AcademicTerm
    {
        return $this->holidays['Easter'];
    }

    public function getSummerHolidays() : AcademicTerm
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

    /* FUNCTIONS */

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
            'weeks' => $this->weeks,
            'days' => $this->days,
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
        $this->holidays['Christmas'] = $this->setChristmasHolidays();
        $this->holidays['Easter'] = $this->setEasterHolidays();
        $this->holidays['Summer'] = $this->setSummerHolidays();
    }

    private function createAcademicTerms()
    {
        $this->terms['Autumn'] = $this->setAutumnTerm();
        $this->terms['Spring'] = $this->setSpringTerm();
        $this->terms['Summer'] = $this->setSummerTerm();
        $this->weeks = $this->terms['Autumn']->getWeekCount() + $this->terms['Spring']->getWeekCount() + $this->terms['Summer']->getWeekCount() - 3;
        $this->days = $this->terms['Autumn']->getDayCount() + $this->terms['Spring']->getDayCount() + $this->terms['Summer']->getDayCount() - 15;
    }

    private function setChristmasHolidays() : AcademicTerm
    {
        $term = new AcademicTerm;

        $term
        ->halfTermActive(false)
        ->setStart($this->getFirstDayOfChristmasHolidays())
        ->setEnd($this->getLastDayOfChristmasHolidays())
        ->setName('Christmas');

        return $term;
    }

    private function setEasterHolidays() : AcademicTerm
    {
        $term = new AcademicTerm;

        $term
        ->halfTermActive(false)
        ->setStart($this->getFirstDayOfEasterHolidays())
        ->setEnd($this->getLastDayOfEasterHolidays())
        ->setName('Easter');

        return $term;
    }

    private function setSummerHolidays() : AcademicTerm
    {
        $term = new AcademicTerm;

        $term
        ->halfTermActive(false)
        ->setStart($this->getFirstDayOfSummerHolidays())
        ->setEnd($this->getLastDayOfSummerHolidays())
        ->setName('Summer');

        return $term;
    }

    private function setAutumnTerm() : AcademicTerm
    {
        $term = new AcademicTerm;
        $stretched = new StretchedTerm($this->getFirstDayOfAutumnTerm(), $this->getLastDayOfChristmasHolidays());

        $term
        ->setStart($this->getFirstDayOfAutumnTerm())
        ->setEnd($this->getLastDayOfAutumnTerm())
        ->setName('Autumn')
        ->setStretched($stretched)
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfAutumnTerm(), $this->getLastDayOfAutumnTerm()));

        return $term;
    }

    private function setSpringTerm() : AcademicTerm
    {
        $term = new AcademicTerm;
        $stretched = new StretchedTerm($this->getFirstDayOfSpringTerm(), $this->getLastDayOfEasterHolidays());

        $term
        ->setStart($this->getFirstDayOfSpringTerm())
        ->setEnd($this->getLastDayOfSpringTerm())
        ->setName('Spring')
        ->setStretched($stretched)
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfSpringTerm(), $this->getLastDayOfEasterHolidays()));

        return $term;
    }

    private function setSummerTerm() : AcademicTerm
    {
        $term = new AcademicTerm;
        $stretched = new StretchedTerm($this->getFirstDayOfSummerTerm(), $this->getLastDayOfSummerHolidays());

        $term
        ->setMonthCount(5)
        ->setStart($this->getFirstDayOfSummerTerm())
        ->setEnd($this->getLastDayOfSummerTerm())
        ->setName('Summer')
        ->setStretched($stretched)
        ->setClosedDates($this->getClosedDates($this->getFirstDayOfSummerTerm(), $this->getLastDayOfSummerHolidays()));

        return $term;
    }

    /* Terms */
    private function getFirstDayOfSpringTerm() : Carbon
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

    private function getLastDayOfSpringTerm() : Carbon
    {
        $holiday_provider = Continuum::getBankHolidayProvider($this->end_year);
        $easter_mid = Carbon::parse('26th march ' . $this->end_year)->addDays(13);

        if ($holiday_provider->getEasterSaturday()->lte($easter_mid)) {
            return $holiday_provider->getGoodFriday()->copy()->subDays(1);
        } else {
            return $holiday_provider->getEasterSaturday()->copy()->subDay(1)->subWeeks(2);
        }
    }

    private function getFirstDayOfSummerTerm() : Carbon
    {
        return $this->getLastDayOfSpringTerm()->copy()->addWeeks(3)->startOfWeek();
    }

    private function getLastDayOfSummerTerm() : Carbon
    {
        return Carbon::parse('last friday of july ' . $this->end_year)->subWeek();
    }

    private function getFirstDayOfAutumnTerm() : Carbon
    {
        return Carbon::parse('first monday of september ' . $this->start_year);
    }

    private function getLastDayOfAutumnTerm() : Carbon
    {
        return $this->getFirstDayOfSpringTerm()->copy()->startOfWeek()->subWeeks(3)->endOfWeek()->subDays(2);
    }

    /* Holidays */
    private function getFirstDayOfSummerHolidays() : Carbon
    {
        return $this->getLastDayOfSummerTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    private function getLastDayOfSummerHolidays() : Carbon
    {
        return Carbon::parse('first monday of september ' . $this->end_year)->subWeeks(1)->endOfWeek()->subDays(2);
    }

    private function getFirstDayOfEasterHolidays() : Carbon
    {
        return $this->getLastDayOfSpringTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    private function getLastDayOfEasterHolidays() : Carbon
    {
        return $this->getFirstDayOfSummerTerm()->copy()->subWeeks(1)->endOfWeek()->subDays(2);
    }

    private function getFirstDayOfChristmasHolidays() : Carbon
    {
        return $this->getLastDayOfAutumnTerm()->copy()->addWeeks(1)->startOfWeek();
    }

    private function getLastDayOfChristmasHolidays() : Carbon
    {
        return $this->getFirstDayOfSpringTerm()->copy()->subWeeks(1)->endOfWeek()->subDays(2);
    }

    private function getClosedDates(Carbon $start_date, Carbon $end_date) : array
    {
        return $this->closed_dates->filter(function ($item) use ($start_date, $end_date) {
            $date = Carbon::createFromFormat('Y-m-d', $item);
            return $start_date->lte($date) && $end_date->gte($date);
        })->toArray();
    }
}
