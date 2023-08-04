<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;

class FieldTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('FieldType',function() {
            return new \App\ReportFieldType\FieldType;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
