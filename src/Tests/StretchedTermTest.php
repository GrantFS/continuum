<?php

namespace Loopy\Continuum\Tests;

use Carbon\Carbon;
use Loopy\Continuum\Classes\Academic\AcademicDates;
use Loopy\Continuum\Classes\Academic\StretchedTerm;
use Tests\TestCase;

class StretchedTermTest extends TestCase
{
    protected $provider;
    /*
    * vendor/phpunit/phpunit/phpunit ../loopy/continuum/tests/StretchedTermTest.php
    */
    public function testGetDays()
    {
        $days = $this->provider->getDays();
        $this->assertTrue(is_int($days->first()));
        $this->assertEquals($days['2020-09-07'], 1);
        $this->assertEquals($days['2020-12-31'], 4);
        $this->assertCount(83, $days);
    }

    public function testGetWeeks()
    {
        $weeks = $this->provider->getWeeks();
        $this->assertInstanceOf(Carbon::class, $weeks->first());
        $this->assertCount(17, $weeks);
        $this->assertEquals($weeks->first()->format('Y-m-d'), '2020-09-07');
        $this->assertEquals($weeks->last()->format('Y-m-d'), '2020-12-28');
    }

    public function testGetMonths()
    {
        $months = $this->provider->getMonths();
        $month_list = [9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
        foreach ($month_list as $id => $month) {
            $this->assertEquals($month, $months[$id]);
        }
    }

    public function testGetDayCount()
    {
        $days = $this->provider->getDayCount();
        $this->assertEquals($days, 82);
        $this->assertTrue(is_int($days));
    }

    public function testGetWeekCount()
    {
        $weeks = $this->provider->getWeekCount();
        $this->assertEquals($weeks, 16);
        $this->assertTrue(is_int($weeks));
    }

    public function testGetMonthCount()
    {
        $months = $this->provider->getMonthCount();
        $this->assertEquals($months, 4);
        $this->assertTrue(is_int($months));
    }

    public function testGetBankHolidays()
    {
        $bank_holidays = $this->provider->getBankHolidays();
        $this->assertInstanceOf(Carbon::class, $bank_holidays->first());
        $this->assertCount(2, $bank_holidays);
        $this->assertEquals($bank_holidays->first()->format('Y-m-d'), '2020-12-25');
        $this->assertEquals($bank_holidays->last()->format('Y-m-d'), '2021-01-01');
    }

    public function testGetClosedDates()
    {
        $closed_dates = $this->provider->getClosedDates();
        $this->assertCount(0, $closed_dates);
        $this->provider->setClosedDates(['2020-09-30']);
        $closed_dates = $this->provider->getClosedDates();
        $this->assertCount(1, $closed_dates);
        $this->assertInstanceOf(Carbon::class, $closed_dates[0]);
        $this->assertEquals($closed_dates[0]->format('Y-m-d'), '2020-09-30');
    }

    public function setUp()
    {
        parent::setUp();
        $start_year = Carbon::createFromFormat('Y', '2020')->format('Y');
        $this->year_provider = new AcademicDates;
        $this->year_provider->setStartYear($start_year);

        $start = $this->year_provider->getFirstDayOfAutumnTerm();
        $end = $this->year_provider->getLastDayOfChristmasHolidays();
        $this->provider = new StretchedTerm($start, $end);
    }
}
