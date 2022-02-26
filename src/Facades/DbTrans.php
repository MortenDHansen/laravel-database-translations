<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Facades;

use Carbon\Carbon;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DbTrans
{
    public function getDatabaseTranslations(string $group, string $locale): array
    {
        $cacheKey = app('dbtrans')->getCacheKey($group, $locale);
        return cache()->remember($cacheKey, Carbon::now()->addDay(), function () use ($group, $locale) {
            $toArray = DatabaseLangItem::where('group', $group)
                ->where('locale', $locale)
                ->get()
                ->mapWithKeys(function (DatabaseLangItem $langItem) {
                    return [$langItem->key => $langItem->value];
                })
                ->toArray();
            return $toArray;
        });
    }

    public static function getCacheKey(string $group, string $locale): string
    {
        if ($group == '*') {
            $group = 'ungrouped';
        }
        return config('translations-database.cache-prefix') . '.' . $locale . '.' . $group;
    }

    public function createLanguageItem(string $group, string $key, string $locale): DatabaseLangItem
    {
        /** @var DatabaseLangItem $lineItem */
        $lineItem = DatabaseLangItem::firstOrCreate([
            'group'  => $group,
            'key'    => $key,
            'locale' => $locale,
        ]);
        if ($lineItem->wasRecentlyCreated) {
            cache()->forget(app('dbtrans')->getCacheKey($group, $locale));
            app('dbtrans')->getDatabaseTranslations($group, $locale);
        }
        return $lineItem;
    }
}