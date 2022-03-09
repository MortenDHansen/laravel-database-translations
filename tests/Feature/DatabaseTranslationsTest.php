<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use MortenDHansen\LaravelDatabaseTranslations\DbTrans;
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
        DatabaseLangItem::factory()->create([
            'group'  => '*',
            'key'    => 'salad',
            'value'  => 'green',
            'locale' => 'en'
        ]);

        $this->assertEquals('green', __('salad'));
    }

    /**
     * @test
     * @return void
     */
    public function loadGroupedTranslationFromDatabase()
    {
        DatabaseLangItem::factory()->create([
            'group'  => 'food',
            'key'    => 'salad',
            'value'  => 'green',
            'locale' => 'en'
        ]);
        $this->assertEquals('green', __('food.salad'));
    }

    /**
     * @test
     * @return void
     */
    public function databaseTranslationsOverrideFiles()
    {
        $this->addPhpTranslationFile(['salad' => 'green'], 'food', 'en');
        DatabaseLangItem::create([
            'group'  => 'food',
            'key'    => 'salad',
            'value'  => 'blue',
            'locale' => 'en'
        ]);
        $this->assertEquals('blue', __('food.salad'));
    }

    /**
     * @test
     * @return void
     */
    public function changesToOverrideTakesImmidiateEffect()
    {
        __('animal');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'locale' => 'en', 'key' => 'animal']);

        $record = DatabaseLangItem::where('group', '*')->where('locale', 'en')->where('key', 'animal')->first();
        $this->assertNull($record->value);

        $record->value = 'bird';
        $record->save();
        $this->assertDatabaseHas('database_lang_items',
            ['group' => '*', 'locale' => 'en', 'key' => 'animal', 'value' => 'bird']);

        $this->assertEquals('bird', __('animal'));
    }

    /**
     * @test
     * @return void
     */
    public function databaseTranslationsOverrideFilesUnlessDatabaseHasNoValue()
    {
        $this->addPhpTranslationFile(['salad' => 'green'], 'food', 'en');
        DatabaseLangItem::create([
            'group'  => 'food',
            'key'    => 'salad',
            'value'  => null,
            'locale' => 'en'
        ]);
        $this->assertEquals('green', __('food.salad'));
    }

    /**
     * @test
     * @return void
     */
    public function itOverridesLaravelsRuleArrays()
    {
        DatabaseLangItem::create([
            'group'  => 'validation',
            'key'    => 'gt',
            'value' => json_encode([
                'array'   => 'From DB The :attribute must have more than :value items.',
                'file'    => 'From DB The :attribute must be greater than :value kilobytes.',
                'numeric' => 'From DB The :attribute must be greater than :value.',
                'string'  => 'From DB The :attribute must be greater than :value characters.',
            ]),
            'locale' => 'en'
        ]);

        $validate = Validator::make(['value' => 1], ['value' => 'gt:100'])->getMessageBag()->toArray();
        $this->assertIsArray($validate);
        $this->assertEquals('From DB The value must be greater than 100.', $validate['value'][0]);
    }

    /**
     * @test
     * @return void
     */
    public function itAllowsCustomValidationMessages()
    {
        $validate = Validator::make(['value' => 1], ['value' => 'gt:100'], [
            'gt' => 'CRAP'
        ])->getMessageBag()->toArray();
        $this->assertIsArray($validate);
        $this->assertEquals('CRAP', $validate['value'][0]);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesMissingKey()
    {
        __('salad');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'locale' => 'en', 'key' => 'salad']);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesMissingGroupedKey()
    {
        __('food.salad');
        $this->assertDatabaseHas('database_lang_items', ['group' => 'food', 'locale' => 'en', 'key' => 'salad']);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesAndCachesMissingGroupedKey()
    {
        __('food.salad');
        $this->assertDatabaseHas('database_lang_items', ['group' => 'food', 'locale' => 'en', 'key' => 'salad']);
        cache()->has(DbTrans::getCacheKey('good', 'en'));
    }

    /**
     * @test
     * @return void
     */
    public function itHandlesOtherLanguage()
    {
        DatabaseLangItem::factory()->create([
            'group'  => '*',
            'key'    => 'salad',
            'value'  => 'green',
            'locale' => 'en'
        ]);

        $this->assertEquals('green', __('salad'));

        app()->setLocale('de');
        $this->assertEquals('salad', __('salad'));

        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'locale' => 'de', 'key' => 'salad']);
    }

    /**
     * @test
     * @return void
     */
    public function commandRebuildsCache()
    {
        // create translations in two languages
        __('salad.tomato');
        __('burger');

        app()->setLocale('de');

        __('salad.tomato');
        __('pasta');

        // Change content of a translation without triggering events (should not be cached)
        DatabaseLangItem::where('locale', 'de')->where('key', 'tomato')->update(['value' => 'schnitzel']);
        $this->assertTrue(\Cache::has(DbTrans::getCacheKey('salad', 'de')));
        $this->assertNull(\Cache::get(DbTrans::getCacheKey('salad', 'de'))['tomato']);
        $this->assertDatabaseHas('database_lang_items', [
            'group'  => 'salad',
            'locale' => 'de',
            'key'    => 'tomato',
            'value'  => 'schnitzel'
        ]);

        // rebuild cache - now cache should be aligned with db
        $this->artisan('dbtrans:cache-rebuild');
        $this->assertTrue(\Cache::has(DbTrans::getCacheKey('salad', 'de')));
        $this->assertEquals('schnitzel', \Cache::get(DbTrans::getCacheKey('salad', 'de'))['tomato']);
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
            'group'  => '*',
            'key'    => 'salad',
            'value'  => 'green',
            'locale' => 'en'
        ]);
        DatabaseLangItem::factory()->create([
            'group'  => '*',
            'key'    => 'salad',
            'value'  => 'schwarz',
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
            'key'    => 'current_password',
            'value'  => 'Database has a different opinion!',
            'group'  => 'validation',
            'locale' => 'en'
        ]);
        $this->assertDatabaseHas('database_lang_items', [
            'group'  => 'validation',
            'locale' => 'en',
            'key'    => 'current_password'
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
            'key'    => 'salad',
            'group'  => '*',
            'value'  => null,
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
        __('validation.current_password');
        $this->assertDatabaseHas('database_lang_items', ['group' => 'validation', 'key' => 'current_password']);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesMissingKeysButDoesNotReturnEmptyValues()
    {
        __('current_password');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'current_password']);
    }

    /**
     * @test
     * @return void
     */
    public function itCreatesMissingKeysInDifferentLocales()
    {
        __('color');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'color', 'locale' => 'en']);

        app()->setLocale('de');

        __('color');
        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'color', 'locale' => 'de']);
    }

    /**
     * @test
     * @return void
     */
    public function itCanHandleChunksOfText()
    {
        __("Sometimes, translations are in fact a chunk of text. It can be very long and it makes no sense to treat it as a grouped key.");
        $this->assertEquals('*', DatabaseLangItem::all()->first()->group);
    }


}