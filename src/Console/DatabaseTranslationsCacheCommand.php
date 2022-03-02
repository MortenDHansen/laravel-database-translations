<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Console;

use MortenDHansen\LaravelDatabaseTranslations\DbTrans;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseTranslationsCacheCommand extends \Illuminate\Console\Command
{

    protected $signature = "dbtrans:cache-rebuild";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge and rebuild cache for database translations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $groups = DatabaseLangItem::all('group')->unique('group')->pluck('group');
        $locales = DatabaseLangItem::all('locale')->unique('locale')->pluck('locale');

        // build cache keys

        foreach ($groups as $group) {
            foreach ($locales as $locale) {
                \Cache::forget(DbTrans::getCacheKey($group, $locale));
                DbTrans::getDatabaseTranslations($group, $locale);
            }
        }

        return self::SUCCESS;
    }

    public function buildCacheKeys()
    {
        return $cacheKeys;
    }
}