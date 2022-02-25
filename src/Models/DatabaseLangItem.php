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
        'locale'
    ];

    protected static function newFactory()
    {
        return DatabaseLangItemFactory::new();
    }
}

