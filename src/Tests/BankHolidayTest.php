<?php

namespace Loopy\Continuum\Tests;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Loopy\Continuum\Classes\BankHolidayProvider;
use Tests\TestCase;

class BankHolidayTest extends TestCase
{
    protected $provider;
    /*
    * vendor/phpunit/phpunit/phpunit ../loopy/continuum/src/tests/BankHolidayTest.php
    */

    public function testGet()
    {
        $bank_holidays = $this->provider->get();
        $this->assertInstanceOf(Collection::class, $bank_holidays);
        $this->assertInstanceOf(Carbon::class, $bank_holidays->first());
        $this->assertEquals('12-25', $bank_holidays->first()->format('m-d'));
    }

    public function testIsBankHoliday()
    {
        $date = Carbon::createFromFormat('m-d', '12-25');
        $this->assertTrue($this->provider->isBankHoliday($date));
        $date = Carbon::createFromFormat('m-d', '12-24');
        $this->assertFalse($this->provider->isBankHoliday($date));
    }

    public function testGetNewYearsDay()
    {
        $date = $this->provider->getNewYearsDay();
        $this->assertEquals('01-01', $date->format('m-d'));
    }

    public function testGetMayBankHolidays()
    {
        $dates = $this->provider->getMayBankHolidays();
        $this->assertTrue(is_array($dates));
        foreach ($dates as $date) {
            $this->assertEquals('05', $date->format('m'));
        }
    }

    public function testGetEasterBankHolidays()
    {
        $dates = $this->provider->getEasterBankHolidays();
        $this->assertTrue(is_array($dates));
        $this->assertTrue($dates[0]->isFriday());
        $this->assertTrue($dates[1]->isMonday());
    }

    public function testGetAugustBankHoliday()
    {
        $date = $this->provider->getAugustBankHoliday();
        $this->assertEquals('08', $date->format('m'));
    }

    public function testGetEasterMonday()
    {
        $date = $this->provider->getEasterMonday();
        $this->assertTrue($date->isMonday());
    }

    public function testGetGoodFriday()
    {
        $date = $this->provider->getGoodFriday();
        $this->assertTrue($date->isFriday());
    }

    public function testGetEasterSaturday()
    {
        $date = $this->provider->getEasterSaturday();
        $this->assertTrue($date->isSaturday());
    }

    public function setUp()
    {
        $start_year = Carbon::now()->format('Y');
        $this->provider = new BankHolidayProvider($start_year);
    }
}
