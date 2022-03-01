<?php

namespace MortenDHansen\LaravelDatabaseTranslations;

use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseTranslationsTranslator extends \Illuminate\Translation\Translator
{

    public array $loadedFromDb = [];


    /**
     * Get the translation for the given key.
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @param bool $fallback
     * @return string|array
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $locale = $locale ?: $this->locale;
        $passedLocale = $locale;

        // load basic translations (json files / ungrouped and non namespaced translations)
        $this->load('*', '*', $locale);

        // Check if translation is found.
        $line = $this->loaded['*']['*'][$locale][$key] ?? null;

        if (!is_null($line)) {
            $this->createMissingKey('*', $key, $locale);
        }

        // If we can't find a translation for the JSON key, we will attempt to translate it
        // using the typical translation file. This way developers can always just use a
        // helper such as __ instead of having to pick between trans or __ with views.
        if (!isset($line)) {
            [$namespace, $group, $item] = $this->parseKey($key);

            // Here we will get the locale that should be used for the language line. If one
            // was not passed, we will use the default locales which was given to us when
            // the translator was instantiated. Then, we can load the lines and return.
            $locales = $fallback ? $this->localeArray($locale) : [$locale];

            foreach ($locales as $locale) {
                $line = $this->getLine(
                    $namespace,
                    $group,
                    $locale,
                    $item,
                    $replace
                );
                if (!is_null($line)) {
                    $this->createMissingKey($group, $item, $locale);
                    return $line;
                }
            }
        }

        // The key was parsed, item place will be null if key is ungrouped
        if (is_null($item)) {
            $item = $group;
            $group = '*';
        }
        $this->createMissingKey($group, $item, $passedLocale);

        // If the line doesn't exist, we will return back the key which was requested as
        // that will be quick to spot in the UI if language keys are wrong or missing
        // from the application's language files. Otherwise we can return the line.
        return $this->makeReplacements($line ?: $key, $replace);
    }

    public function createMissingKey($group, $item, $locale)
    {
        DatabaseLangItem::create([
            'group'  => $group,
            'key'    => $item,
            'locale' => $locale,
            'value'  => null
        ]);
        $this->loaded = [];
        $this->loadedFromDb = [];
    }

    /**
     * Load the specified language group.
     *
     * @param string $namespace
     * @param string $group
     * @param string $locale
     * @return void
     */
    public function load($namespace, $group, $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }

        // The loader is responsible for returning the array of language lines for the
        // given namespace, group, and locale. We'll set the lines in this array of
        // lines that have already been loaded so that we can easily access them.

        $lines = app('translation.loader')->load($locale, $group, $namespace);
        $fileLines = app('translation.file-loader')->load($locale, $group, $namespace);

        $this->loaded[$namespace][$group][$locale] = array_merge($fileLines, $lines);
        $this->loadedFromDb[$namespace][$group][$locale] = $lines;
    }
}