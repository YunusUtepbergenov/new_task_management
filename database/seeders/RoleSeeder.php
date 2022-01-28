<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([[
            'name' => "Директор"
        ],
        [
            'name' => "Заведующий сектором"
        ],
        [
            'name' => "Главный научный сотрудник"
        ],
        [
            'name' => "Ведущий научный сотрудник"
        ],
        [
            'name' => "Главный бухгалтер"
        ],
        [
            'name' => "Бухгалтер"
        ],
        [
            'name' => "Спецалист по работе с персоналом"
        ],
        [
            'name' => "Юрисконсульт"
        ],
        [
            'name' => "Заведующий канцелярии"
        ],
        [
            'name' => "Библиотекарь"
        ],
        [
            'name' => "ИКТ спецалист"
        ],
        [
            'name' => "Зав.Хоз"
        ],
        [
            'name' => "Референт"
        ]
        ]);
    }
}
