<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use Illuminate\Contracts\Translation\Loader;

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
        $databaseTranslations = app('dbtrans')->getDatabaseTranslations($group, $locale);
        $this->dbTranslations[$group][$locale] = $databaseTranslations;

        return array_filter($this->dbTranslations[$group][$locale],
            function ($translationItemValue, $translationItemKey) {
                return !is_null($translationItemValue);
            }, ARRAY_FILTER_USE_BOTH);
    }
}