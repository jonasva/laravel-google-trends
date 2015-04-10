<?php namespace Jonasva\LaravelGoogleTrends\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelGoogleTrends extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-google-trends';
    }
}
