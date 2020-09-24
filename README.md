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

convertToDayName : string
Pass in a day number and get the day name.

getMonthsBetween : DatePeriod
Pass in a start and end date and get a list of months in that range.

firstWeekOfMonth : Carbon
Pass in a carbon date and get the first monday of the first week in that month.

lastWeekOfMonth : Carbon
Pass in a carbon date and get the last monday of the last week in that month.

getRange : DatePeriod
Pass in a start and end date and get the range.

getMonthsRange : DatePeriod

Returns 6 months before and after today as a range.

getWeeksFor : DatePeriod
Pass in the start of the month, return a range of the weeks in that month.

convertMonthSelect : Carbon
Pass in a monthyear, get a carbon for the 1st of that month or first weekday

getYearSelect : array
Pass in the number of years, returns the years as an array

getWeeklyDates : DatePeriod
Pass a start date, return the dates in that week

getMonths : array
Returns an array of month_number => Month Name

getDueDate : string
Pass in the string day and return the Y-m-d for the closest in the future.

getDaysInWeek : DatePeriod
Pass in a date, returns the range of that week

monthStart : Carbon
Pass Month and year, returns start of month carbon

monthEnd : Carbon
Pass Month and year, returns end of month carbon

isDay: bool
Pass in day of the week and date, returns true/false



