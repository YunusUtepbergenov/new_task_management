<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("users")->insert([
        [
            "name" => "А.Иванов",
            "sector_id" => 1,
            "role_id" => 1,
            "email" => "a.ivanov@example.com",
            "phone" => "(90) 100-00-01",
            "internal" => "401",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Б.Петров",
            "sector_id" => 1,
            "role_id" => 14,
            "email" => "b.petrov@example.com",
            "phone" => "(90) 100-00-02",
            "internal" => "433",
            "password" => Hash::make("password")
        ],
        [
            "name" => "В.Сидоров",
            "sector_id" => 1,
            "role_id" => 14,
            "email" => "v.sidorov@example.com",
            "phone" => "(90) 100-00-03",
            "internal" => "409",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Г.Ташматов",
            "sector_id" => 4,
            "role_id" => 2,
            "email" => "g.tashmatov@example.com",
            "phone" => "(90) 100-00-04",
            "internal" => "416",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Д.Каримов",
            "sector_id" => 5,
            "role_id" => 2,
            "email" => "d.karimov@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Е.Рахимов",
            "sector_id" => 6,
            "role_id" => 2,
            "email" => "e.rakhimov@example.com",
            "phone" => "(90) 100-00-06",
            "internal" => "410",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ж.Назаров",
            "sector_id" => 7,
            "role_id" => 2,
            "email" => "zh.nazarov@example.com",
            "phone" => "(90) 100-00-07",
            "internal" => "406",
            "password" => Hash::make("password")
        ],
        [
            "name" => "З.Султанова",
            "sector_id" => 8,
            "role_id" => 2,
            "email" => "z.sultanova@example.com",
            "phone" => "(90) 100-00-08",
            "internal" => "417",
            "password" => Hash::make("password")
        ],
        [
            "name" => "И.Юсупов",
            "sector_id" => 9,
            "role_id" => 2,
            "email" => "i.yusupov@example.com",
            "phone" => "(90) 100-00-09",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "К.Алиев",
            "sector_id" => 2,
            "role_id" => 3,
            "email" => "k.aliev@example.com",
            "phone" => "(90) 100-00-10",
            "internal" => "421",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Л.Мирзаев",
            "sector_id" => 2,
            "role_id" => 4,
            "email" => "l.mirzaev@example.com",
            "phone" => "(90) 100-00-11",
            "internal" => "430",
            "password" => Hash::make("password")
        ],
        [
            "name" => "М.Хасанова",
            "sector_id" => 2,
            "role_id" => 4,
            "email" => "m.khasanova@example.com",
            "phone" => "(90) 100-00-12",
            "internal" => "427",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Н.Турсунов",
            "sector_id" => 3,
            "role_id" => 3,
            "email" => "n.tursunov@example.com",
            "phone" => "(90) 100-00-13",
            "internal" => "431",
            "password" => Hash::make("password")
        ],
        [
            "name" => "О.Бекмуратов",
            "sector_id" => 3,
            "role_id" => 3,
            "email" => "o.bekmuratov@example.com",
            "phone" => "(90) 100-00-14",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "П.Шарипов",
            "sector_id" => 3,
            "role_id" => 4,
            "email" => "p.sharipov@example.com",
            "phone" => "(90) 100-00-15",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Р.Ахмедов",
            "sector_id" => 4,
            "role_id" => 3,
            "email" => "r.akhmedov@example.com",
            "phone" => "(90) 100-00-16",
            "internal" => "419",
            "password" => Hash::make("password")
        ],
        [
            "name" => "С.Джураев",
            "sector_id" => 4,
            "role_id" => 3,
            "email" => "s.djuraev@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Т.Закирова",
            "sector_id" => 4,
            "role_id" => 4,
            "email" => "t.zakirova@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "У.Норматов",
            "sector_id" => 5,
            "role_id" => 3,
            "email" => "u.normatov@example.com",
            "phone" => "(90) 100-00-19",
            "internal" => "413",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.Исматов",
            "sector_id" => 6,
            "role_id" => 3,
            "email" => "f.ismatov@example.com",
            "phone" => "(90) 100-00-20",
            "internal" => "441",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Х.Нуриева",
            "sector_id" => 6,
            "role_id" => 4,
            "email" => "kh.nurieva@example.com",
            "phone" => "(90) 100-00-21",
            "internal" => "411",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ц.Маматов",
            "sector_id" => 7,
            "role_id" => 3,
            "email" => "ts.mamatov@example.com",
            "phone" => "(90) 100-00-22",
            "internal" => "429",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ш.Азимова",
            "sector_id" => 7,
            "role_id" => 3,
            "email" => "sh.azimova@example.com",
            "phone" => "(90) 100-00-23",
            "internal" => "418",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Щ.Холматов",
            "sector_id" => 7,
            "role_id" => 4,
            "email" => "shch.kholmatov@example.com",
            "phone" => "(90) 100-00-24",
            "internal" => "436",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Э.Саидов",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "e.saidov@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ю.Файзуллаев",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "yu.fayzullaev@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Я.Муминова",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "ya.muminova@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Б.Усманов",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "a.usmanov@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Б.В.Гафурова",
            "sector_id" => 8,
            "role_id" => 4,
            "email" => "b.gafurova@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "В.Г.Ташпулатов",
            "sector_id" => 9,
            "role_id" => 4,
            "email" => "v.tashpulatov@example.com",
            "phone" => "(90) 100-00-30",
            "internal" => "450",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Г.Д.Содиков",
            "sector_id" => 9,
            "role_id" => 4,
            "email" => "g.sodikov@example.com",
            "phone" => "(90) 100-00-31",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Д.Е.Эргашев",
            "sector_id" => 9,
            "role_id" => 4,
            "email" => "d.ergashev@example.com",
            "phone" => "(90) 100-00-32",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Е.Ж.Кадыров",
            "sector_id" => 10,
            "role_id" => 3,
            "email" => "e.kadyrov@example.com",
            "phone" => "(90) 100-00-33",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ж.З.Расулов",
            "sector_id" => 10,
            "role_id" => 3,
            "email" => "zh.rasulov@example.com",
            "phone" => "(90) 100-00-34",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "З.И.Хайдаров",
            "sector_id" => 10,
            "role_id" => 3,
            "email" => "z.khaydarov@example.com",
            "phone" => "(90) 100-00-35",
            "internal" => "415",
            "password" => Hash::make("password")
        ],
        [
            "name" => "И.К.Сафаров",
            "sector_id" => 11,
            "role_id" => 2,
            "email" => "i.safarov@example.com",
            "phone" => "(90) 100-00-36",
            "internal" => "423",
            "password" => Hash::make("password")
        ],
        [
            "name" => "К.Л.Мухамедов",
            "sector_id" => 11,
            "role_id" => 3,
            "email" => "k.mukhamedov@example.com",
            "phone" => "(90) 100-00-37",
            "internal" => "420",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Л.М.Бахтияров",
            "sector_id" => 11,
            "role_id" => 3,
            "email" => "l.bakhtiyarov@example.com",
            "phone" => "(90) 100-00-38",
            "internal" => "422",
            "password" => Hash::make("password")
        ],
        [
            "name" => "М.Н.Латипов",
            "sector_id" => 11,
            "role_id" => 4,
            "email" => "m.latipov@example.com",
            "phone" => "(90) 100-00-39",
            "internal" => "407",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Н.О.Умаров",
            "sector_id" => 12,
            "role_id" => 3,
            "email" => "n.umarov@example.com",
            "phone" => "(90) 100-00-40",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "О.П.Эшматов",
            "sector_id" => 12,
            "role_id" => 4,
            "email" => "o.eshmatov@example.com",
            "phone" => "(90) 100-00-41",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "П.Р.Бердиев",
            "sector_id" => 13,
            "role_id" => 3,
            "email" => "p.berdiev@example.com",
            "phone" => "(90) 100-00-42",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Р.С.Олимов",
            "sector_id" => 13,
            "role_id" => 3,
            "phone" => "",
            "internal" => "",
            "email" => "r.olimov@example.com",
            "password" => Hash::make("password")
        ],
        [
            "name" => "С.Т.Жалилов",
            "sector_id" => 13,
            "role_id" => 4,
            "phone" => "",
            "internal" => "",
            "email" => "s.zhalilov@example.com",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Т.У.Рустамова",
            "sector_id" => 14,
            "role_id" => 3,
            "email" => "t.rustamova@example.com",
            "phone" => "(90) 100-00-45",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "У.Ф.Набиев",
            "sector_id" => 15,
            "role_id" => 3,
            "email" => "u.nabiev@example.com",
            "phone" => "(90) 100-00-46",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.Х.Тошев",
            "sector_id" => 16,
            "role_id" => 2,
            "email" => "f.toshev@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Х.Ц.Валиев",
            "sector_id" => 16,
            "role_id" => 3,
            "email" => "kh.valiev@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ц.Ш.Муратов",
            "sector_id" => 16,
            "role_id" => 4,
            "email" => "ts.muratov@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ш.Щ.Кузнецова",
            "sector_id" => 1,
            "role_id" => 5,
            "email" => "sh.kuznetsova@example.com",
            "phone" => "(90) 100-00-50",
            "internal" => "414",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Щ.Э.Джамалов",
            "sector_id" => 1,
            "role_id" => 6,
            "email" => "shch.dzhamalov@example.com",
            "phone" => "(90) 100-00-51",
            "internal" => "432",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Э.Ю.Сулейманова",
            "sector_id" => 1,
            "role_id" => 7,
            "email" => "e.suleymanova@example.com",
            "phone" => "(90) 100-00-52",
            "internal" => "425",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ю.Я.Баширов",
            "sector_id" => 1,
            "role_id" => 8,
            "email" => "yu.bashirov@example.com",
            "phone" => "(90) 100-00-53",
            "internal" => "424",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Я.А.Тагиева",
            "sector_id" => 1,
            "role_id" => 9,
            "email" => "ya.tagieva@example.com",
            "phone" => "(90) 100-00-54",
            "internal" => "426",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Б.Махмудова",
            "sector_id" => 1,
            "role_id" => 10,
            "email" => "a.makhmudova@example.com",
            "phone" => "(90) 100-00-55",
            "internal" => "438",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Б.В.Халилов",
            "sector_id" => 1,
            "role_id" => 11,
            "email" => "b.khalilov@example.com",
            "phone" => "(90) 100-00-56",
            "internal" => "444",
            "password" => Hash::make("password")
        ],
        [
            "name" => "В.Г.Нурматова",
            "sector_id" => 1,
            "role_id" => 12,
            "email" => "v.nurmatova@example.com",
            "phone" => "(90) 100-00-57",
            "internal" => "404",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Г.Д.Хамраев",
            "sector_id" => 1,
            "role_id" => 13,
            "email" => "g.khamraev@example.com",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
    ]);
    }
}
