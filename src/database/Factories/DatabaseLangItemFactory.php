<?php

namespace MortenDHansen\LaravelDatabaseTranslations\database\Factories;

use MortenDHansen\LaravelDatabaseTranslations\Models\DatabaseLangItem;

class DatabaseLangItemFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{

    protected $model = DatabaseLangItem::class;

    /**
     * @inheritDoc
     */
    public function definition()
    {
        return [
            'locale' => $this->faker->randomElement(['en', 'da', 'de']),
            'group' => $this->faker->optional(0.4, '*')->randomElement(['empire', 'rebellion']),
            'key' => $this->faker->word(),
            'value' => $this->faker->word(),
        ];
    }
}