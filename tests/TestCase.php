<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests;

use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public array $addedFiles = [];

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

        foreach ($this->addedFiles as $file) {
            unlink($file);
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
        $app['config']->set('cache.file', [
            'driver' => 'file',
            'path' => __DIR__ . '/temp/cache'
        ]);
        $app['path.lang'] = __DIR__ . '/lang';
        $app['config']->set('translations-database.cache-prefix', 'laravel-db-translations');
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
        file_put_contents(__DIR__ . '/lang/' . $locale . '.json', json_encode($content));
    }

    public function addPhpTranslationFile(array $content, $group, $locale = 'en')
    {
        if (is_null($content)) {
            $content = [];
        }
        $output = "<?php " . PHP_EOL . "return [" . PHP_EOL;
        foreach ($content as $key => $value) {
            $output .= sprintf("'%s' => '%s',", $key, $value) . PHP_EOL;
        }
        $output .= '];';

        file_put_contents(__DIR__ . '/lang/' . $locale . '/' . $group . '.php', $output);
        $this->addedFiles[] = __DIR__ . '/lang/' . $locale . '/' . $group . '.php';
    }

}