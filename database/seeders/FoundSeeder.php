<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class FoundSeeder extends Seeder
{
    public function run()
    {
        DB::table('found')->insert([
            ['name' => 'Потеряно'],
            ['name' => 'Найдено'],
        ]);
    }
}