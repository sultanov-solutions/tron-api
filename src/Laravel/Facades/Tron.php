<?php

declare(strict_types=1);

namespace IEXBase\TronAPI\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \IEXBase\TronAPI\Tron make(string $name = null, array $override = []) Create a fresh Tron instance for the given connection and options
 */
class Tron extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tron';
    }
}
