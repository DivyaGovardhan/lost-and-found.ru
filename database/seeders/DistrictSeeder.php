<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run()
    {
        $districts = [
            'Октябрьский',
            'Ленинский',
            'Советский',
            'Кировский'
        ];

        foreach ($districts as $district) {
            DB::table('districts')->insert(['name' => $district]);
        }
    }
}