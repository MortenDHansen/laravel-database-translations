<?php
//
//namespace MortenDHansen\LaravelDatabaseTranslations\Tests\Feature;
//
//use Illuminate\Foundation\Testing\RefreshDatabase;
//
//class DbTransTest extends \MortenDHansen\LaravelDatabaseTranslations\Tests\TestCase
//{
//    use RefreshDatabase;
//
//    /**
//     * @test
//     * @return void
//     */
//    public function itGetsTheCacheKey()
//    {
//        $cacheKey = app('dbtrans')->getCacheKey('some', 'en');
//        $this->assertEquals(config('translations-database.cache-prefix') . '.en.some', $cacheKey);
//    }
//
//    /**
//     * @test
//     * @return void
//     */
//    public function itCreateAndCashes()
//    {
//        app('dbtrans')->createLanguageItem('*', 'testkey', 'en');
//        $this->assertDatabaseHas('database_lang_items', ['group' => '*', 'key' => 'testkey', 'locale' => 'en']);
//        $this->assertArrayHasKey('testkey', app('dbtrans')->getDatabaseTranslations('*', 'en'));
//
//        $key = app('dbtrans')->getCacheKey('*', 'en');
//        $this->assertArrayHasKey('testkey', cache()->get($key));
//    }
//}