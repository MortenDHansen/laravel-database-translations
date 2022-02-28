<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use Illuminate\Contracts\Translation\Loader;
use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseTranslationsLoader implements Loader
{

    public function load($locale, $group, $namespace = null)
    {
        return DatabaseLangItem::where('locale', $locale)
            ->where('group', $group)
            ->get()
            ->mapWithKeys(function (DatabaseLangItem $langItem) {
                $result = [$langItem->key => $langItem->value];
                if (is_array(unserialize($langItem->value))) {
                    $result = [];
                    foreach (unserialize($langItem->value) as $subKey => $subValue) {
                        $result[$langItem->key . '.' . $subKey] = $subValue;
                    }
                }
                return $result;
            })->toArray();
    }

    public function addNamespace($namespace, $hint)
    {
        //
    }

    public function addJsonPath($path)
    {
        //
    }

    public function namespaces()
    {
        //
    }
}