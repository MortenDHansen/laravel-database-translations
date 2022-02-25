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


}