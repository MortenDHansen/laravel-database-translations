<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use Carbon\Carbon;
use Illuminate\Contracts\Translation\Loader;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseTranslationsLoader implements Loader
{

    /**
     * @var string[]
     */
    public array $dbTranslations;

    public function load($locale, $group, $namespace = null)
    {
        if ($group === '*' && $namespace === '*') {
            $dbTranslations = $this->getDbTranslations($group, $locale);
            $fileTranslations = app('translation.file-loader')->load($locale, $group, $namespace);
            return array_merge($fileTranslations, $dbTranslations);
        }

        if (is_null($namespace) || $namespace === '*') {
            $dbTranslations = $this->getDbTranslations($group, $locale);
            $fileTranslations = app('translation.file-loader')->load($locale, $group, $namespace);

            return array_merge($fileTranslations, $dbTranslations);
        }

        // we dont do namespaced - just forward it...
        return app('translation.file-loader')->load($locale, $group, $namespace);
    }

    public function addNamespace($namespace, $hint)
    {
        // TODO: Implement addNamespace() method.
    }

    public function addJsonPath($path)
    {
        // TODO: Implement addJsonPath() method.
    }

    public function namespaces()
    {
        // TODO: Implement namespaces() method.
    }

    /**
     * @param string $group
     * @param string $locale
     * @return mixed
     */
    public function getDbTranslations(string $group, string $locale)
    {
        $cacheKey = $this->getCacheKey($group, $locale);
        if(!cache()->has($cacheKey)) {
            $dbTranslations = DatabaseLangItem::where('group', $group)
                ->where('locale', $locale)
                ->get()
                ->mapWithKeys(function (DatabaseLangItem $langItem
                ) {
                    return [$langItem->key => $langItem->value];
                })
                ->toArray();
            cache()->set($cacheKey, $dbTranslations, Carbon::now()->addDay());
        }
        $this->dbTranslations = cache()->get($cacheKey);

        return array_filter($this->dbTranslations, function ($translationItemValue, $translationItemKey) {
            return !is_null($translationItemValue);
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function createMissing(string $group, string $locale, string $key): bool
    {
        return DatabaseLangItem::createLanguageItem(
            $group,
            $key,
            $locale,
        )->wasRecentlyCreated;
    }

    public static function getCacheKey(string $group, string $locale)
    {
        if($group == '*') {
            $group = 'ungrouped';
        }
        return 'laravel-db-translations.'. $locale . '.'. $group;
    }
}