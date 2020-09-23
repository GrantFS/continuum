<?php

namespace Loopy\Continuum\Facades;

use Illuminate\Support\Facades\Facade;

class ContinuumFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'continuum';
    }
}
