<?php

namespace GregPriday\ZyteApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GregPriday\ZyteApi\ZyteApi
 */
class ZyteApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \GregPriday\ZyteApi\ZyteApi::class;
    }
}
