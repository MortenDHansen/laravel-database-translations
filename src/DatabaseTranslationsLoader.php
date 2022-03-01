<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use Illuminate\Contracts\Translation\Loader;

class DatabaseTranslationsLoader implements Loader
{

    public function load($locale, $group, $namespace = null)
    {
        return DbTrans::getDatabaseTranslations($group, $locale);
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