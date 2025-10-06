<?php

namespace ESolution\LaravelEmail;

use Illuminate\Support\ServiceProvider;
use ESolution\LaravelEmail\Support\MailManager;

class LaravelEmailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel_email.php', 'laravel_email');

        $this->app->singleton(MailManager::class, function($app){
            return new MailManager(config('laravel_email'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel_email.php' => config_path('laravel_email.php'),
        ], 'laravel-email-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'laravel-email-migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
