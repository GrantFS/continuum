# Continuum

## Installation

Add to composer.json

```

"repositories": [
    {
        "url": "https://github.com/GrantFS/continuum.git",
        "type": "vcs"
    }
]

```

```

composer require loopy/continuum


```

In config/app.php

```
'providers' => [
    ...
    Loopy\Continuum\ContinuumServiceProvider::class
]

'aliases' => [
    ...
    'Continuum' => Loopy\Continuum\Facades\ContinuumFacade::class
]


```

## Use

### Comparisons

```

Continuum::compare()

```


getMinutesBetween($start_time, $end_time) : int
returns the difference in minutes

getHoursBetween($start_time, $end_time) : int
returns the difference in hours

getDaysBetween($start_time, $end_time) : DatePeriod
returns a date period of days between

getWeeksBetween($start_time, $end_time) : DatePeriod
returns a date period of weeks between

getMonthsBetween($start_time, $end_time) : DatePeriod
returns a date period of months between


### Conversions

```

Continuum::convert()

```


dayNumberToName($day_number) : string
returns the name of the day of the week

monthNumberToName($month_number) : string
returns a month name

monthSelectToDate($month_year, $first_weekday) : Carbon
returns a carbon of a month select

decimalTimeToDate($time) : Carbon
returns a carbon of the time passed in as a decimal

toMinute($time) : int
returns minutes of the time

toHour($time) : int
returns hours of the time

### Others

getMonthsRange : DatePeriod
returns 6 months before and after today as a range.

getWeeksFor : DatePeriod
Pass in the start of the month, return a range of the weeks in that month.

get7DatesFrom : DatePeriod
Pass a start date, return the dates in that week

firstWeekOfMonth : Carbon
return the first monday of the first week in that month.

lastWeekOfMonth : Carbon
return the last monday of the last week in that month.

monthStart : Carbon
return start of month

monthEnd : Carbon
return end of month

getNextDate : string
Pass in the string day, return the Y-m-d for the closest in the future.

isDay: bool
returns true/false

isOverDue: bool
returns true/false

getDatesBetween : array
returns the dates between 2 dates

getYearSelect : array
Pass in the number of years, returns the years as an array

getMonths : array
Returns an array of month_number => Month Name

getTaxYearMonths : array
returns array of months in tax year


## Continuum Special Classes

### AcademicYear

> A Academic Year is made up of Academic Terms.

```
    /* Return All the Terms for 2020 */
    Continuum::academicYear(2020)->getTerms();
```
### StretchedAcademicYear

> A Stretched Year is made up of Streched Terms.  A Stretched Term is a different type of academic term where the holidays are included in the term.

```
    /* Return All the Stretched Terms for 2020 */
    Continuum::stretchedAcademicYear(2020)->getTerms();
```
### BankHolidays

```
    /* Return All the Bank Holidays for 2020 */
    Continuum::bankHolidays(2020)->get();
```
