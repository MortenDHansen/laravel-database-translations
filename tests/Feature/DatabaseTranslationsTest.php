<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

        // database says salad is green
        $dbTranslation = DatabaseLangItem::factory()->create([
            'key' => 'salad',
            'value' => 'green'
        ]);
        $this->assertEquals('blue', __('salad'));
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
            'group' => 'validation'
        ]);

        $this->assertEquals('Database has a different opinion!', __('validation.current_password'));
    }

}