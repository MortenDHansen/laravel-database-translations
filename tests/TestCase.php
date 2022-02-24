<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests;

use Illuminate\Support\Facades\Config;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            DatabaseTranslationsServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('locale', 'en');
        $app['config']->set('fallback_locale', 'en');
        $app['path.lang'] = __DIR__ . '/lang';
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function overrideApplicationBindings($app)
    {
        return [
            'Illuminate\Translation\TranslationServiceProvider' => 'MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider',
        ];
    }
}