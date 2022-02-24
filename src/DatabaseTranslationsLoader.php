<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use Illuminate\Contracts\Translation\Loader;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseTranslationsLoader implements Loader
{

    public function load($locale, $group, $namespace = null)
    {
        if ($group === '*' && $namespace === '*') {
            $dbTranslations = DatabaseLangItem::where('group', $group)
                ->where('locale', $locale)
                ->get()
                ->mapWithKeys(function (DatabaseLangItem $langItem
                ) {
                    return [$langItem->key => $langItem->value];
                })
                ->toArray();
            $fileTranslations = app('translation.file-loader')->load($locale, $group, $namespace);
            return array_merge($fileTranslations, $dbTranslations);
        }

        if (is_null($namespace) || $namespace === '*') {
            $dbTranslations = DatabaseLangItem::where('group', $group)
                ->where('locale', $locale)
                ->get()
                ->mapWithKeys(function (DatabaseLangItem $langItem
                ) {
                    return [$langItem->key => $langItem->value];
                })
                ->toArray();
            $fileTranslations = app('translation.file-loader')->load($locale, $group, $namespace);

            return array_merge($fileTranslations, $dbTranslations);
        }

        // we dont do namespaced...
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
}