<?php

namespace MortenDHansen\LaravelDatabaseTranslations\Facades;


class DatabaseTranslationsFacade extends \Illuminate\Support\Facades\Facade
{
    public static function getFacadeAccessor()
    {
        return 'dbtrans';
    }
}