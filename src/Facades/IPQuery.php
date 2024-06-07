<?php

namespace QbCloud\Geoip2\Facades;

use Illuminate\Support\Facades\Facade;

class IPQuery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'geoip2';
    }
}
