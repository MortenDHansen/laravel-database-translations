# laravel-database-translations
[![run-tests](https://github.com/MortenDHansen/laravel-database-translations/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/MortenDHansen/laravel-database-translations/actions/workflows/run-tests.yml)

**Laravel translations managed in database**

enables Laravels translations to be overridden, added or edited in database.

Laravel ships with [localization](https://laravel.com/docs/9.x/localization) built around a file structure that can be
used in two ways:

- a .json file per language in the /lang directory, or
- php files returning an array nested in folders where first level is the locale

As a developer, it can be cumbersome to keep multiple language files updated with the keys - and sometimes your
stakeholders / customers or user may want to participate in translating the software.

This package will _front_ laravels localization so that

- translation/localization can happen in database
- missing keys are created on the fly, so you don't have to worry about missing keys in certain locales
- if a value is not set in the database, or a key is missing, it falls back to laravels defaults

Eg, you may use a translation like this:

```php
__('animal') 
```

The key will be created in the table for the current locale. So, if you have visitors from 10 different countries, it
will update the table whenever the key is encountered and found missing.

If you have an en.json that sets `{"animal":"bird"}`, the result of the above will be 'bird'. You may now override that
in the database.

## Installation

```shell
composer require mortendhansen/laravel-database-translations
```

Publish the migration:

```shell
artisan vendor:publish --provider="MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider" --tag="migrations"
artisan migrate
```

Now you need to replace the laravel TranslationServiceProvider with the service provider from the package

```php
\MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsServiceProvider::class,
// Illuminate\Translation\TranslationServiceProvider::class, 
```

## Remarks

- Of course getting, and writing language keys whenever they appear is quite a heavy load of you have many of them.
  Therefore the package caches the keys. When cache is cold, the pages may be quite slow for the first couple of
  requests.
