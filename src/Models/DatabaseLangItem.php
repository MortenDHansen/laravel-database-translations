<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MortenDHansen\LaravelDatabaseTranslations\database\Factories\DatabaseLangItemFactory;

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
            cache()->forget(app('dbtrans')->getCacheKey($model->group, $model->locale));
            app('dbtrans')->getDatabaseTranslations($model->group, $model->locale);
        });

        static::updated(function ($model) {
            cache()->forget(app('dbtrans')->getCacheKey($model->group, $model->locale));
            app('dbtrans')->getDatabaseTranslations($model->group, $model->locale);
        });
    }
}

