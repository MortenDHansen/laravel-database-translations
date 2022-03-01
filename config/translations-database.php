<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Loaded translations from database are cached by the default cache selected
    |
    */
    'cache-prefix' => 'laravel-db-translations',

    'cache-driver' => config('cache.default')
];