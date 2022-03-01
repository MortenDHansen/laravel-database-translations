<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Models;

use Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MortenDHansen\LaravelDatabaseTranslations\database\Factories\DatabaseLangItemFactory;
use MortenDHansen\LaravelDatabaseTranslations\DbTrans;

class DatabaseLangItem extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'locale',
        'value'
    ];

    protected static function newFactory()
    {
        return DatabaseLangItemFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            Cache::driver(config('translations-database.cache-driver'))->forget(DbTrans::getCacheKey($model->group,
                $model->locale));
            DbTrans::getDatabaseTranslations($model->group, $model->locale);
        });

        static::updated(function ($model) {
            Cache::driver(config('translations-database.cache-driver'))->forget(DbTrans::getCacheKey($model->group,
                $model->locale));
            DbTrans::getDatabaseTranslations($model->group, $model->locale);
        });
    }
}

