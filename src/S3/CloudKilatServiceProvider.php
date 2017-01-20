<?php

namespace Reka\S3;

use Illuminate\Support\ServiceProvider;

class CloudKilatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/cloudkilatstorage.php' => config_path('cloudkilatstorage.php'),
        ]);        
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        \App::bind('cloudKilat', function()
        {
            return new CloudKilatStorage;
        });        
    }
}
