<?php

namespace Loopy\Continuum\Tests;

use Carbon\Carbon;
use Loopy\Continuum\Classes\Academic\AcademicDates;
use Tests\TestCase;

class AcademicDatesTest extends TestCase
{
    protected $provider;
    /*
    * vendor/phpunit/phpunit/phpunit ../loopy/continuum/tests/AcademicDatesTest.php
    */

    public function testFirstDayOfSummerTerm()
    {
        $date = $this->provider->getFirstDayOfSummerTerm();
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->month == 4);
        $this->assertTrue($date->isMonday());
    }

    public function testLastDaySummerTerm()
    {
        $date = $this->provider->getLastDayOfSummerTerm();
        $this->assertTrue($date->isFriday());
        $this->assertTrue($date->month == 7);
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->day > 14 && $date->day <= 24);
    }

    public function testFirstDayOfAutumnTerm()
    {
        $date = $this->provider->getFirstDayOfAutumnTerm();
        $this->assertTrue($date->isMonday());
        $this->assertTrue($date->month == 9);
        $this->assertTrue($date->year == $this->provider->getStartYear());
        $this->assertTrue($date->day >= 1 && $date->day <= 7);
    }

    public function testLastDayOfAutumnTerm()
    {
        $date = $this->provider->getLastDayOfAutumnTerm();
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->isFriday());
        $this->assertTrue($date->month == 12);
        $this->assertTrue($date->day > 14);
    }

    public function testFirstDayOfSummerHolidays()
    {
        $date = $this->provider->getFirstDayOfSummerHolidays();
        $this->assertTrue($date->isMonday());
        $this->assertTrue($date->month == 7);
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->day > 20 && $date->day <= 31);
    }

    public function testLastDayOfSummerHolidays()
    {
        $date = $this->provider->getLastDayOfSummerHolidays();
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->month == 9 || $date->month == 8);
        $this->assertTrue($date->isFriday());
    }

    public function testFirstDayOfEasterHolidays()
    {
        $date = $this->provider->getFirstDayOfEasterHolidays();
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->month >= 3 && $date->month <= 4);
        $this->assertTrue($date->isMonday());
    }

    public function testLastDayOfEasterHolidays()
    {
        $date = $this->provider->getLastDayOfEasterHolidays();
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->month == 4);
        $this->assertTrue($date->isFriday());
    }

    public function testFirstDayOfChristmasHolidays()
    {
        $date = $this->provider->getFirstDayOfChristmasHolidays();
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->isMonday());
        $this->assertTrue($date->month == 12);
        $this->assertTrue($date->day > 14);
    }

    public function testLastDayOfChristmasHolidays()
    {
        $date = $this->provider->getLastDayOfChristmasHolidays();
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->isFriday());
        $this->assertTrue($date->month == 12 || $date->month == 1);
        $this->assertTrue($date->day >= 29 || $date->day < 5);
    }

    public function testFirstDayOfSpringTerm()
    {
        $date = $this->provider->getFirstDayOfSpringTerm();
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->isMonday() || $date->isTuesday());
        $this->assertTrue($date->month == 1);
        $this->assertTrue($date->day > 1 && $date->day <= 7);
    }

    public function testLastDayOfSpringTerm()
    {
        $date = $this->provider->getLastDayOfSpringTerm();
        $this->assertTrue($date->year == $this->provider->getEndYear());
        $this->assertTrue($date->hour == 0);
        $this->assertTrue($date->minute == 0);
        $this->assertTrue($date->month >= 3 && $date->month <= 4);
        $this->assertTrue($date->isThursday() || $date->isFriday());
    }

    public function testLongTerm()
    {
        parent::setUp();
        echo PHP_EOL;
        $count = 10;
        while ($count > 0) {
            $this->provider->setStartYear($this->provider->getStartYear() + 1);
            $this->testFirstDayOfSummerTerm();
            $this->testFirstDayOfAutumnTerm();
            $this->testLastDayOfAutumnTerm();
            $this->testLastDaySummerTerm();
            $this->testLastDayOfEasterHolidays();
            $this->testFirstDayOfChristmasHolidays();
            $this->testFirstDayOfSummerHolidays();
            $this->testLastDayOfSummerHolidays();
            $this->testFirstDayOfEasterHolidays();
            $this->testLastDayOfChristmasHolidays();
            $this->testFirstDayOfSpringTerm();
            $this->testLastDayOfSpringTerm();
            echo $this->provider->getStartYear() . ' passed' . PHP_EOL;
            $count --;
        }
    }

    public function setUp()
    {
        $start_year = Carbon::now()->addYears(0)->format('Y');
        $this->provider = new AcademicDates;
        $this->provider->setStartYear($start_year);
    }
}
