<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests;

use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            DatabaseTranslationsServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
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