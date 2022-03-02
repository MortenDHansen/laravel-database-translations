<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use Illuminate\Translation\FileLoader;
use Illuminate\Translation\TranslationServiceProvider;
use MortenDHansen\LaravelDatabaseTranslations\Console\DatabaseTranslationsCacheCommand;

class DatabaseTranslationsServiceProvider extends TranslationServiceProvider
{
    public function register()
    {
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new DatabaseTranslationsTranslator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/translations-database.php', 'translation-database');

        $this->app->singleton('dbtrans', function ($app) {
            return new DbTrans($app);
        });
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function () {
            return new DatabaseTranslationsLoader();
        });

        $this->app->singleton('translation.file-loader', function ($app) {
            return new FileLoader($app['files'], $app['path.lang']);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $destination = database_path('migrations/' . date('Y_m_d_His',
                    time()) . '_create_database_language_items_table.php');
            $this->publishes([
                __DIR__ . '/database/migrations/create_database_language_items_table.php.stub' => $destination,
            ], 'migrations');

            $this->publishes([
                __DIR__ . '/../config/translations-database.php' => config_path('translations-database.php'),
            ]);

            $this->commands([
                DatabaseTranslationsCacheCommand::class
            ]);
        }
    }
}