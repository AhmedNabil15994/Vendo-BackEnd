<?php

namespace App\Setting\Providers;

use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
//        dd(config_path(__DIR__.'/../../config/setting.php'));
//        $this->publishes([
//            __DIR__.'/../../database/migrations/' => database_path('migrations'),
//        ], 'setting');

        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/config/setting.php' => config_path('setting.php'),
        ], 'setting');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__.'/config/setting.php',
        'setting'
        );
        $settingModel = config('laravel-setting.model', \App\Setting\EloquentStorage::class);
        // Register the service the package provides.
        $this->app->bind('Setting', \App\Setting\Setting::class);
        $this->app->bind(\App\Setting\Contracts\SettingStorageContract::class, $settingModel);
    }
}
