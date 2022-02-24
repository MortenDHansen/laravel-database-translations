<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests\Feature;

use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsLoader;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsTranslator;

class BaseicTest extends \MortenDHansen\LaravelDatabaseTranslations\Tests\TestCase
{

    /**
     * @test
     * @return void
     */
    public function itBootsCoreClassOverrides()
    {
        $this->assertInstanceOf(DatabaseTranslationsTranslator::class, $this->app['translator']);
        $this->assertInstanceOf(DatabaseTranslationsLoader::class, $this->app['translation.loader']);
    }

}