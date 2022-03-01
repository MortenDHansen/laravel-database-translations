<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Facades;

use Carbon\Carbon;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DbTrans
{
    public static function getDatabaseTranslations(string $group, string $locale): array
    {
        $cacheKey = DbTrans::getCacheKey($group, $locale);
        return \Cache::driver(config('translations-database.cache-driver'))->remember($cacheKey,
            Carbon::now()->addDay(), function () use ($group, $locale) {
                return DatabaseLangItem::where('locale', $locale)
                    ->where('group', $group)
                    ->get()
                    ->mapWithKeys(function (DatabaseLangItem $langItem) {
                        $result = [$langItem->key => $langItem->value];
                        if (is_array(json_decode($langItem->value, true))) {
                            $result = [];
                            foreach (json_decode($langItem->value, true) as $subKey => $subValue) {
                                $result[$langItem->key . '.' . $subKey] = $subValue;
                            }
                    }
                    return $result;
                })->toArray();
        });
    }

    public static function getCacheKey(string $group, string $locale): string
    {
        if ($group == '*') {
            $group = 'ungrouped';
        }
        return config('translations-database.cache-prefix') . '.' . $locale . '.' . $group;
    }

    public static function createLanguageItem(string $group, string $key, string $locale): DatabaseLangItem
    {
        /** @var DatabaseLangItem $langItem */
        $langItem = DatabaseLangItem::firstOrCreate([
            'group'  => $group,
            'key'    => $key,
            'locale' => $locale,
        ], [
            'value' => null
        ]);

        if (!$langItem->wasRecentlyCreated) {
            // it was already there!
            // ToDo Handle Laravels validation custom attribute calls @see \Illuminate\Validation\Concerns\FormatsMessages::getCustomMessageFromTranslator
            // ToDo Handle Array updates
        }

        return $langItem;
    }
}