<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MortenDHansen\LaravelDatabaseTranslations\database\Factories\DatabaseLangItemFactory;
use MortenDHansen\LaravelDatabaseTranslations\DatabaseTranslationsLoader;

class DatabaseLangItem extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;
    protected $fillable = [
        'group',
        'key',
        'locale'
    ];

    public static function createLanguageItem($group, $key, $locale): DatabaseLangItem
    {
        /** @var DatabaseLangItem $lineItem */
        $lineItem = self::updateOrCreate([
            'group'  => $group,
            'key'    => $key,
            'locale' => $locale,
        ]);
        if($lineItem->wasRecentlyCreated) {
            cache()->forget(DatabaseTranslationsLoader::getCacheKey($group, $locale));
        }
        return $lineItem;
    }

    protected static function newFactory()
    {
        return DatabaseLangItemFactory::new();
    }
}

