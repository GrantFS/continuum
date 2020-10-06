<?php

namespace Loopy\Continuum\Tests;

use Carbon\Carbon;
use Loopy\Continuum\Classes\Academic\StretchedAcademicYear;
use Loopy\Continuum\Classes\Academic\Term;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    protected $provider;
    /*
    * vendor/phpunit/phpunit/phpunit ../loopy/continuum/src/tests/StretchedAcademicYearTest.php
    */

    public function testGetTerms()
    {
        $terms = $this->provider->getTerms();
        $this->assertCount(3, $terms);
        $this->assertInstanceOf(Term::class, $terms->first());
    }

    public function testGetHolidays()
    {
        # code..
        $holidays = $this->provider->getHolidays();
        $this->assertCount(3, $holidays);
        $this->assertInstanceOf(Term::class, $holidays->first());
    }

    public function testGetAutumnTerm()
    {
        $term = $this->provider->getAutumnTerm();
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals('Autumn', $term->getName());
    }

    public function testGetSpringTerm()
    {
        $term = $this->provider->getSpringTerm();
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals('Spring', $term->getName());
    }

    public function testGetSummerTerm()
    {
        $term = $this->provider->getSummerTerm();
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals('Summer', $term->getName());
    }

    public function testGetChristmasHolidays()
    {
        $term = $this->provider->getChristmasHolidays();
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals('Christmas', $term->getName());
    }

    public function testGetEasterHolidays()
    {
        $term = $this->provider->getEasterHolidays();
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals('Easter', $term->getName());
    }

    public function testGetSummerHolidays()
    {
        $term = $this->provider->getSummerHolidays();
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals('Summer', $term->getName());
    }

    public function testGetCurrentTermName()
    {
        $term_name = $this->provider->getCurrentTermName();
        $month = Carbon::now()->format('M');
        $name = in_array($month, [9,10,11,12]) ? 'Autumn' : (in_array($month, [1,2,3]) ? 'Spring' : (in_array($month, [4,5,6,7]) ? 'Summer' : null));
        if ($name) {
            $this->assertEquals($name, $term_name);
        }
    }

    public function testGetPreviousTermName()
    {
        $term_name = $this->provider->getPreviousTermName();
        $month = Carbon::now()->format('M');
        $name = in_array($month, [9,10,11,12]) ? 'Summer' : (in_array($month, [1,2,3]) ? 'Autumn' : (in_array($month, [4,5,6,7]) ? 'Spring' : null));
        if ($name) {
            $this->assertEquals($name, $term_name);
        }
    }

    public function testGetNextTermName()
    {
        $term_name = $this->provider->getNextTermName();
        $month = Carbon::now()->format('M');
        $name = in_array($month, [9,10,11,12]) ? 'Spring' : (in_array($month, [1,2,3]) ? 'Summer' : (in_array($month, [4,5,6,7]) ? 'Autumn' : null));
        if ($name) {
            $this->assertEquals($name, $term_name);
        }
    }

    public function testGetCurrentTerm()
    {
        $term = $this->provider->getCurrentTerm();
        $this->assertInstanceOf(Term::class, $term);
    }

    public function testGetNextTerm()
    {
        $term = $this->provider->getNextTerm();
        $this->assertInstanceOf(Term::class, $term);
    }

    public function testGetPreviousTerm()
    {
        $term = $this->provider->getPreviousTerm();
        $this->assertInstanceOf(Term::class, $term);
    }

    public function testCountAllTermWeeks()
    {
        $term_weeks = $this->provider->countAllTermWeeks();
        $this->assertTrue(is_int($term_weeks));
    }

    public function testCountAllWeeksInSpring()
    {
        $term_weeks = $this->provider->countAllWeeksInSpring();
        $this->assertTrue(is_int($term_weeks));
    }

    public function testCountAllWeeksInAutumn()
    {
        $term_weeks = $this->provider->countAllWeeksInAutumn();
        $this->assertTrue(is_int($term_weeks));
    }

    public function testCountAllWeeksInSummer()
    {
        $term_weeks = $this->provider->countAllWeeksInSummer();
        $this->assertTrue(is_int($term_weeks));
    }

    public function testTerm()
    {
        $term = $this->provider->term('autumn');
        $this->assertInstanceOf(Term::class, $term);
    }

    public function setUp()
    {
        parent::setUp();
        $start_year = Carbon::createFromFormat('Y', '2020')->format('Y');
        $this->provider = new StretchedAcademicYear($start_year);
    }
}
