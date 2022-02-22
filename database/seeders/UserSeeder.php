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
            "name" => "О.Хакимов",
            "sector_id" => 1,
            "role_id" => 1,
            "email" => "o.khakimov@cerr.uz",
            "phone" => "(97) 770-15-46",
            "internal" => "401",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Н.К.Ортиков",
            "sector_id" => 2,
            "role_id" => 2,
            "email" => "n.ortiqov@cerr.uz",
            "phone" => "(99) 972-22-61",
            "internal" => "433",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Х.С.Асадов",
            "sector_id" => 3,
            "role_id" => 2,
            "email" => "k.asadov@cerr.uz",
            "phone" => "(90) 960-16-24",
            "internal" => "409",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.Д.Давлетов",
            "sector_id" => 4,
            "role_id" => 2,
            "email" => "f.davletov@cerr.uz",
            "phone" => "(90) 357-00-71",
            "internal" => "416",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Нигманов",
            "sector_id" => 5,
            "role_id" => 2,
            "email" => "a.nigmonov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Х.С.Хамидов",
            "sector_id" => 6,
            "role_id" => 2,
            "email" => "k.khamidov@cerr.uz",
            "phone" => "(90) 997-87-53",
            "internal" => "410",
            "password" => Hash::make("password")
        ],
        [
            "name" => "М.М.Холмухамедов",
            "sector_id" => 7,
            "role_id" => 2,
            "email" => "m.kholmukhamedov@cerr.uz",
            "phone" => "(90) 372-99-34",
            "internal" => "406",
            "password" => Hash::make("password")
        ],
        [
            "name" => "З.А.Ризаева",
            "sector_id" => 8,
            "role_id" => 2,
            "email" => "z.rizaeva@cerr.uz",
            "phone" => "(90) 174-13-30",
            "internal" => "417",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Шамсиев Ж",
            "sector_id" => 9,
            "role_id" => 2,
            "email" => "j.shamsiyev@cerr.uz",
            "phone" => "(99) 311-66-84",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ю.Ш.Кутбитдинов",
            "sector_id" => 2,
            "role_id" => 3,
            "email" => "y.qutbitdinov@cerr.uz",
            "phone" => "(93) 588-40-57",
            "internal" => "421",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ш.К.Бобожанов",
            "sector_id" => 2,
            "role_id" => 4,
            "email" => "b.shakhrukh@cerr.uz",
            "phone" => "(91) 275-07-08",
            "internal" => "430",
            "password" => Hash::make("password")
        ],
        [
            "name" => "М.Н.МирАхмадова",
            "sector_id" => 2,
            "role_id" => 4,
            "email" => "m.mirakhmadova@cerr.uz",
            "phone" => "(91) 408-55-58",
            "internal" => "427",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Х.З.Мажидов",
            "sector_id" => 3,
            "role_id" => 3,
            "email" => "x.majidov@cerr.uz",
            "phone" => "(90) 951-11-10",
            "internal" => "431",
            "password" => Hash::make("password")
        ],
        [
            "name" => "О.О.Одилов",
            "sector_id" => 3,
            "role_id" => 3,
            "email" => "o.odilov@cerr.uz",
            "phone" => "(94) 614-03-10",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "К.Нуриллаев",
            "sector_id" => 3,
            "role_id" => 4,
            "email" => "k.nurillaev@cerr.uz",
            "phone" => "(94) 715-02-04",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Р.В.Абатуров",
            "sector_id" => 4,
            "role_id" => 3,
            "email" => "r.abaturov@cerr.uz",
            "phone" => "(93) 572-65-09",
            "internal" => "419",
            "password" => Hash::make("password")
        ],
        [
            "name" => "И.Худайберганов",
            "sector_id" => 4,
            "role_id" => 3,
            "email" => "i.khudayberganov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Мирошникова Д.",
            "sector_id" => 4,
            "role_id" => 4,
            "email" => "d.miroshnikova@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Р.Р.Абдуллаев",
            "sector_id" => 5,
            "role_id" => 3,
            "email" => "r.abdullaev@cerr.uz",
            "phone" => "(97) 708-59-31",
            "internal" => "413",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Х.Камалов",
            "sector_id" => 6,
            "role_id" => 3,
            "email" => "a.kamalov@cerr.uz",
            "phone" => "(93) 562-47-76",
            "internal" => "441",
            "password" => Hash::make("password")
        ],
        [
            "name" => "М.Б.Жуманиёзова",
            "sector_id" => 6,
            "role_id" => 4,
            "email" => "m.jumaniyozova@cerr.uz",
            "phone" => "(99) 002-94-71",
            "internal" => "411",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Т.Бозоров",
            "sector_id" => 7,
            "role_id" => 3,
            "email" => "a.bozorov@cerr.uz",
            "phone" => "(93) 714-22-50",
            "internal" => "429",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Н.Б.Курбанбаева",
            "sector_id" => 7,
            "role_id" => 3,
            "email" => "n.kurbanbaeva@cerr.uz",
            "phone" => "(97) 755-25-48",
            "internal" => "418",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Б.А.Исмоилов",
            "sector_id" => 7,
            "role_id" => 4,
            "email" => "b.ismailov@cerr.uz",
            "phone" => "(93) 185-65-86",
            "internal" => "436",
            "password" => Hash::make("password")
        ],
        [
            "name" => "З.Анваров",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "z.anvarov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Б.Туракулов",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "b.to'raqulov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Г.Азимова",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "g.azimova@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "У.Мамасалаев",
            "sector_id" => 8,
            "role_id" => 3,
            "email" => "u.mamasalaeva@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Л.А.Буриева",
            "sector_id" => 8,
            "role_id" => 4,
            "email" => "l.buriyeva@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ю.Утепбергенов",
            "sector_id" => 9,
            "role_id" => 4,
            "email" => "y.utepbergenov@cerr.uz",
            "phone" => "(93) 366-75-00",
            "internal" => "450",
            "password" => Hash::make("yu3667500")
        ],
        [
            "name" => "И.Раббимов",
            "sector_id" => 9,
            "role_id" => 4,
            "email" => "i.rabbimov@cerr.uz",
            "phone" => "(97) 923-01-24",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.Туткабаев",
            "sector_id" => 9,
            "role_id" => 4,
            "email" => "f.tutkabaev@cerr.uz",
            "phone" => "(99) 756-52-00",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "М.К.Бабаданов",
            "sector_id" => 10,
            "role_id" => 3,
            "email" => "m.babadjanov@cerr.uz",
            "phone" => "(97) 401-10-70",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Халмурзаев",
            "sector_id" => 10,
            "role_id" => 3,
            "email" => "a.halmurzaev@cerr.uz",
            "phone" => "(99) 855-14-64",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Т.Ниязматов",
            "sector_id" => 10,
            "role_id" => 3,
            "email" => "t.niyazmatov@cerr.uz",
            "phone" => "(91) 162-62-00",
            "internal" => "415",
            "password" => Hash::make("password")
        ],
        [
            "name" => "В.Абатуров",
            "sector_id" => 11,
            "role_id" => 2,
            "email" => "v.abaturov@cerr.uz",
            "phone" => "(71) 277-78-69",
            "internal" => "423",
            "password" => Hash::make("password")
        ],
        [
            "name" => "В.Оганьян",
            "sector_id" => 11,
            "role_id" => 3,
            "email" => "v.oganyan@cerr.uz",
            "phone" => "(93) 520-42-97",
            "internal" => "420",
            "password" => Hash::make("password")
        ],
        [
            "name" => "C.Абатуров",
            "sector_id" => 11,
            "role_id" => 3,
            "email" => "s.abaturov@cerr.uz",
            "phone" => "(90) 320-75-14",
            "internal" => "422",
            "password" => Hash::make("password")
        ],
        [
            "name" => "В.Луконин",
            "sector_id" => 11,
            "role_id" => 4,
            "email" => "v.lukonin@cerr.uz",
            "phone" => "(97) 777-39-15",
            "internal" => "407",
            "password" => Hash::make("password")
        ],
        [
            "name" => "С.Бегманов",
            "sector_id" => 12,
            "role_id" => 3,
            "email" => "s.begmanov@cerr.uz",
            "phone" => "(99) 583-52-57",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ю.Жумабаев",
            "sector_id" => 12,
            "role_id" => 4,
            "email" => "y.jumabaev@cerr.uz",
            "phone" => " (97) 508-04-90",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.Рахимов",
            "sector_id" => 13,
            "role_id" => 3,
            "email" => "f.raximov@cerr.uz",
            "phone" => "(90) 296-01-44",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "У.Хужакулов",
            "sector_id" => 13,
            "role_id" => 3,
            "phone" => "",
            "internal" => "",
            "email" => "u.khujakulov@cerr.uz",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Э.Пулатов",
            "sector_id" => 13,
            "role_id" => 4,
            "phone" => "",
            "internal" => "",
            "email" => "e.pulatov@cerr.uz",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ш.Нурдинова",
            "sector_id" => 14,
            "role_id" => 3,
            "email" => "sh.nurdinova@cerr.uz",
            "phone" => "(90) 158-53-68",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Б.Хусанов",
            "sector_id" => 15,
            "role_id" => 3,
            "email" => "b.khusanov@cerr.uz",
            "phone" => "(91) 555-21-27",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.Кобилов",
            "sector_id" => 16,
            "role_id" => 2,
            "email" => "a.kobilov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Э.Зайниддинов",
            "sector_id" => 16,
            "role_id" => 3,
            "email" => "e.zayniddinov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.Исломов",
            "sector_id" => 16,
            "role_id" => 4,
            "email" => "f.islomov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Н.Н.Ли",
            "sector_id" => 1,
            "role_id" => 5,
            "email" => "n.li@cerr.uz",
            "phone" => "(90) 371-18-77",
            "internal" => "414",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.А.Абдукаххоров",
            "sector_id" => 1,
            "role_id" => 6,
            "email" => "a.abduqaxxorov@cerr.uz",
            "phone" => "(90) 110-04-88",
            "internal" => "432",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Ф.А.Абдурахмонова",
            "sector_id" => 1,
            "role_id" => 7,
            "email" => "f.abduraxmonova@cerr.uz",
            "phone" => "(94) 602-43-45",
            "internal" => "425",
            "password" => Hash::make("password")
        ],
        [
            "name" => "А.А.Манунцев",
            "sector_id" => 1,
            "role_id" => 8,
            "email" => "a.manuncev@cerr.uz",
            "phone" => "(90) 976-87-53",
            "internal" => "424",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Д.Закирова",
            "sector_id" => 1,
            "role_id" => 9,
            "email" => "d.zakirova@cerr.uz",
            "phone" => "(93) 563-75-54",
            "internal" => "426",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Н.Очилова",
            "sector_id" => 1,
            "role_id" => 10,
            "email" => "n.ochilova@cerr.uz",
            "phone" => "(90) 788-78-91",
            "internal" => "438",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Р.Мардонов",
            "sector_id" => 1,
            "role_id" => 11,
            "email" => "neoradmin@cerr.uz",
            "phone" => "(99) 908-52-20",
            "internal" => "444",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Г.Матрузиева",
            "sector_id" => 1,
            "role_id" => 12,
            "email" => "g.matruzayeva@cerr.uz",
            "phone" => "(97) 177-54-47",
            "internal" => "404",
            "password" => Hash::make("password")
        ],
        [
            "name" => "Шамсиддинов Ш.",
            "sector_id" => 1,
            "role_id" => 13,
            "email" => "sh.shamsiddinov@cerr.uz",
            "phone" => "",
            "internal" => "",
            "password" => Hash::make("password")
        ],
    ]);
    }
}
