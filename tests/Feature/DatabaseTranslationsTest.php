<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsLoader;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseTranslationsTest extends \MortenDHansen\LaravelDatabaseTranslations\Tests\TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function itLoadsTranslationFromDatabase()
    {
        $this->addTranslationFile(['salad' => 'blue']);
        $dbTranslation = DatabaseLangItem::factory()->create([
            'group' => '*',
            'key' => 'salad',
            'value' => 'green',
            'locale' => 'en'
        ]);

        $this->assertEquals('green', __('salad'));
    }

    /**
     * @test
     * @return void
     */
    public function itLoadsTranslationFromDatabaseInDifferentLocales()
    {
        $this->addTranslationFile(['salad' => 'blue']);
        $this->addTranslationFile(['salad' => 'schwarz'], 'de');
        DatabaseLangItem::factory()->create([
            'group' => '*',
            'key' => 'salad',
            'value' => 'green',
            'locale' => 'en'
        ]);
        DatabaseLangItem::factory()->create([
            'group' => '*',
            'key' => 'salad',
            'value' => 'schwarz',
            'locale' => 'de'
        ]);

        $this->assertEquals('green', __('salad'));

        app()->setLocale('de');

        $this->assertEquals('schwarz', __('salad'));
    }

    /**
     * @test
     * @return void
     */
    public function itLoadsTranslationFromFile()
    {
        // file says salad is blue
        $this->addTranslationFile(['salad' => 'blue']);
        $this->assertEquals('blue', __('salad'));
    }

    /**
     * @test
     * @return void
     */
    public function ifTranslationIsBothInDatabaseAndFilesDatabaseWins()
    {
        // file says salad is blue
        $this->addTranslationFile(['salad' => 'blue']);
        $this->assertEquals('blue', __('salad'));

        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'locale' => 'en', 'key' => 'salad']);
        $langLine = DatabaseLangItem::where('key', 'salad')->first();
        $langLine->value = 'green';
        $langLine->save();
        $this->assertEquals('green', __('salad'));
    }

    /**
     * @test
     * @return void
     */
    public function itLoadsGroupedTranslationFromFile()
    {
        $this->assertEquals('The password is incorrect.', __('validation.current_password'));
    }

    /**
     * @test
     * @return void
     */
    public function ifGroupedTranslationIsBothInDatabaseAndFilesDatabaseWins()
    {
        DatabaseLangItem::factory()->create([
            'key' => 'current_password',
            'value' => 'Database has a different opinion!',
            'group' => 'validation',
            'locale' => 'en'
        ]);
        $this->assertDatabaseHas('database_lang_items', [
            'group' => 'validation',
            'locale' => 'en',
            'key' => 'current_password'
        ]);

        $this->assertEquals('Database has a different opinion!', __('validation.current_password'));
    }

    /**
     * @test
     * @return void
     */
    public function itTakesTranslationFromFileIfDatabaseHasEmptyValue()
    {
        $this->addTranslationFile(['salad' => 'blue']);

        DatabaseLangItem::factory()->create([
            'key' => 'salad',
            'group' => '*',
            'value' => null,
            'locale' => 'en'
        ]);

        $this->assertEquals('blue', __('salad'));
        $this->assertDatabaseHas('database_lang_items', ['key' => 'salad', 'locale' => 'en']);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesMissingGroupedKeysButDoesNotReturnEmptyValues()
    {
        $translated = __('validation.current_password');
        $this->assertDatabaseHas('database_lang_items', ['group' => 'validation', 'key' => 'current_password']);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesMissingKeysButDoesNotReturnEmptyValues()
    {
        $translated = __('current_password');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'current_password']);
    }
    /**
     * @test
     * @return void
     */
    public function itCreatesMissingKeysInDifferentLocales()
    {
        $translated = __('color');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'color', 'locale' => 'en']);

        app()->setLocale('de');

        $translated = __('color');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'color', 'locale' => 'de']);

    }

    /**
     * @test
     * @return void
     */
    public function loaderReturnsItemsWhichHasValue()
    {
        DatabaseLangItem::create([
            'group' => '*',
            'locale' => 'en',
            'key' => 'somekey'
        ]);

        DatabaseLangItem::create([
            'group' => '*',
            'locale' => 'en',
            'key' => 'someotherkey',
            'value' => 'withvalue'
        ]);

        $loader = new DatabaseTranslationsLoader();
        $result = $loader->getDbTranslations('*', 'en');
        $this->assertCount(1, $result);
        $this->assertCount(2, $loader->dbTranslations['*']['en']);
        $this->assertArrayNotHasKey('somekey', $result);
        $this->assertArrayHasKey('someotherkey', $result);
    }

    /**
     * @test
     * @return void
     */
    public function itFiguresOutMixedCaseKeys()
    {
        $key = app('dbtrans')->getCacheKey('*', 'en');
        __('Im an annoying Key!');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'Im an annoying Key!', 'locale' => 'en']);
        $line = DatabaseLangItem::where('key', 'Im an annoying Key!')->first();
        $line->value = 'blabla';
        $line->save();
        cache()->forget($key);
        $this->assertEquals('blabla', __('Im an annoying Key!'));
    }

    /**
     * @test
     * @return void
     */
    public function itFiguresOutMixedCaseGroupedKeys()
    {
        __('metallica.Im an annoying Key!');
        $this->assertDatabaseHas('database_lang_items',
            ['group' => 'metallica', 'key' => 'Im an annoying Key!', 'locale' => 'en']);
        $line = DatabaseLangItem::where('key', 'Im an annoying Key!')->where('group', 'metallica')->first();
        $line->value = 'blabla';
        $line->save();
        cache()->forget(app('dbtrans')->getCacheKey('metalica', 'en'));

        $this->assertEquals('blabla', __('metallica.Im an annoying Key!'));
    }


}