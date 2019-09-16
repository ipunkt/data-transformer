<?php

namespace Ipunkt\DataTransformer;

use Illuminate\Support\ServiceProvider;
use Ipunkt\DataTransformer\Console\Commands\TransformData;
use Ipunkt\DataTransformer\Console\Commands\TransformDump;

class DataTransformerServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton('command.ipunkt.data-transformer', function ($app) {
            return $app['Ipunkt\DataTransformer\Console\Commands\TransformData}'];
        });

        $this->app->singleton('command.ipunkt.data-transformer', function ($app) {
            return $app['Ipunkt\DataTransformer\Console\Commands\TransformDump'];
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TransformData::class,
                TransformDump::class,
            ]);
        }

        $this->mergeConfigFrom(
            __DIR__.'/config/data-transformer.php', 'data-transformer'
        );

        $this->publishes([
            __DIR__.'/config/data-transformer.php' => config_path('data-transformer.php'),
        ]);
    }
}
