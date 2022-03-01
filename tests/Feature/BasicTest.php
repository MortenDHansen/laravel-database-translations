<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MortenDHansen\LaravelDatabaseTranslations\Contracts\DbTrans;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsLoader;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsTranslator;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class BasicTest extends \MortenDHansen\LaravelDatabaseTranslations\Tests\TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function itBootsCoreClassOverrides()
    {
        $this->assertInstanceOf(DatabaseTranslationsTranslator::class, $this->app['translator']);
        $this->assertInstanceOf(DatabaseTranslationsLoader::class, $this->app['translation.loader']);
    }

    /**
     * @test
     * @return void
     */
    public function itReturnLanguageKeyAsTranslationIfNoTranslationIsProvided()
    {
        // '__(' and 'trans(' is the same
        $this->assertEquals('something', __('something'));
        $this->assertEquals('something', trans('something'));
    }

    /**
     * @test
     * @return void
     */
    public function testCachesValues()
    {
        cache()->set('somekey', 'somevalue');
        $this->assertEquals('somevalue', cache()->get('somekey'));
    }

    /**
     * @test
     * @return void
     */
    public function itReturnsTranslatedStringIfTranslationFileIsPresent()
    {
        $this->addTranslationFile(['salad' => 'blue']);
        $this->assertEquals('blue', __('salad'));
        $this->assertEquals('blue', trans('salad'));
    }

    /**
     * @test
     * @return void
     */
    public function databaseMayHaveTranslations()
    {
        DatabaseLangItem::factory()->count(10)->create();
        $this->assertDatabaseCount('database_lang_items', 10);
    }

}