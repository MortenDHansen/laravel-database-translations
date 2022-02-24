<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

class DatabaseTranslationsTranslator extends \Illuminate\Translation\Translator
{

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        return parent::get($key, $replace, $locale, $fallback);
    }
}