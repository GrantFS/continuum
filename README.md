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



firstWeekOfMonth : Carbon
return the first monday of the first week in that month.

lastWeekOfMonth : Carbon
return the last monday of the last week in that month.

getMonthsRange : DatePeriod
Returns 6 months before and after today as a range.

getWeeksFor : DatePeriod
Pass in the start of the month, return a range of the weeks in that month.

getYearSelect : array
Pass in the number of years, returns the years as an array

get7DatesFrom : DatePeriod
Pass a start date, return the dates in that week

getMonths : array
Returns an array of month_number => Month Name

getDueDate : string
Pass in the string day and return the Y-m-d for the closest in the future.

monthStart : Carbon
Pass Month and year, returns start of month carbon

monthEnd : Carbon
Pass Month and year, returns end of month carbon

isDay: bool
Pass in day of the week and date, returns true/false



