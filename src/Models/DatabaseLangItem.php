<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Models;

use Carbon\Carbon;
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

    /**
     * @param string $group
     * @param string $locale
     * @param int|\DateTimeInterface|null $cacheTtl
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getByGroupAndLocale(string $group, string $locale, int|\DateTimeInterface $cacheTtl = null): array
    {
        $cacheKey = DatabaseTranslationsLoader::getCacheKey($group, $locale);
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
        return cache()->get($cacheKey);
    }

    protected static function newFactory()
    {
        return DatabaseLangItemFactory::new();
    }
}

