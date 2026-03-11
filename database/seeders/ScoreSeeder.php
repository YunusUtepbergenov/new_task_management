<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoreSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('scores')->insert([
            ['id' => 1, 'name' => '1. Подготовка докладной записки', 'min_score' => 0, 'max_score' => 40],
            ['id' => 2, 'name' => '2. Подготовка расширенных обзоров и исследований', 'min_score' => 0, 'max_score' => 100],
            ['id' => 3, 'name' => '3. Внедрение передовых методологий', 'min_score' => 0, 'max_score' => 100],
        ]);

        DB::table('types')->insert([
            ['id' => 1, 'name' => 'Письмо'],
        ]);

        DB::table('priorities')->insert([
            ['id' => 1, 'name' => 'Обычный'],
        ]);
    }
}
