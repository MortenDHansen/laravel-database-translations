<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests;

use Illuminate\Support\Facades\Config;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function removeTestLangFiles(): void
    {
        $files = glob(__DIR__ . '/lang/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        // additional setup
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeTestLangFiles();
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
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function overrideApplicationBindings($app)
    {
        return [
            'Illuminate\Translation\TranslationServiceProvider' => 'MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider',
        ];
    }

    public function addTranslationFile(array $content = null, $locale = 'en')
    {
        if (is_null($content)) {
            $content = [];
        }
        file_put_contents(__DIR__.'/lang/'.$locale.'.json', json_encode($content));
    }

}