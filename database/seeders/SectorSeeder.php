<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sectors')->insert([[
            'name' => "АУП"
        ],
        [
            'name' => "Сектор по изучению конкурентоспособности отраслей экономики и инвестиционной активности"
        ],
        [
            'name' => "Сектор по социально-экономическу развитию регионов"
        ],
        [
            'name' => "Сектор по развитию деятелности промышленных кластеров"
        ],
        [
            'name' => "Сектор по изучению согласованности параметров макроэкономической политики и прогнозирования"
        ],
        [
            'name' => "Сектор по изучению деятелности в банковско-финансовой сфере и на рынке капитала"
        ],
        [
            'name' => "Сектор по изучению внешнеэкономической деятельности интеграционный процессов"
        ],
        [
            'name' => "Сектор по связям с общественностью и маркетинга"
        ],
        [
            'name' => "Сектор по внедрению IT технологий и интеграций баз данных"
        ],
        [
            'name' => "Акселератор социално-экономических реформ"
        ],
        [
            'name' => "Редакция журнала Экономическое обозрение"
        ],
        ]);

    }
}
