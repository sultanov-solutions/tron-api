<?php

declare(strict_types=1);

namespace IEXBase\TronAPI\Laravel;

use Illuminate\Support\ServiceProvider;
use IEXBase\TronAPI\Tron;

class TronServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/tron.php', 'tron');

        // Manager binding
        $this->app->singleton(TronManager::class, function($app){
            return new TronManager($app);
        });

        // Expose manager as 'tron' so usage is explicit: app('tron')->make([...])
        $this->app->singleton('tron', function($app){
            return $app->make(TronManager::class);
        });
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../../config/tron.php' => config_path('tron.php'),
        ], 'tron-config');
    }
}
