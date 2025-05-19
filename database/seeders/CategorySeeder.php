<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Документы',
            'Телефоны',
            'Ключи',
            'Сумки и рюкзаки',
            'Одежда',
            'Украшения',
            'Животные',
            'Другое'
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert(['name' => $category]);
        }
    }
}