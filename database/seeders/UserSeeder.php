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
        DB::table('users')->insert([
        [
            'name' => "О.Хакимов",
            'sector_id' => 1,
            'role_id' => 1,
            'email' => "o.khakimov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Н.К.Ортиков",
            'email' => "n.ortiqov@cerr.uz",
            'sector_id' => 2,
            'role_id' => 2,
            'password' => Hash::make('password')
        ],
        [
            'name' => "Х.С.Асадов",
            'sector_id' => 3,
            'role_id' => 2,
            'email' => "k.asadov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ф.Д.Давлетов",
            'sector_id' => 4,
            'role_id' => 2,
            'email' => "f.davletov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.Нигманов",
            'sector_id' => 5,
            'role_id' => 2,
            'email' => "a.nigmonov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Х.С.Хамидов",
            'sector_id' => 6,
            'role_id' => 2,
            'email' => "k.khamidov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "М.М.Холмухамедов",
            'sector_id' => 7,
            'role_id' => 2,
            'email' => "m.kholmukhamedov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "З.А.Ризаева",
            'sector_id' => 8,
            'role_id' => 2,
            'email' => "z.rizaeva@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Шамсиев Ж",
            'sector_id' => 9,
            'role_id' => 2,
            'email' => "j.shamsiyev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ю.Ш.Кутбитдинов",
            'sector_id' => 2,
            'role_id' => 3,
            'email' => "y.qutbitdinov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ш.К.Бобожанов",
            'sector_id' => 2,
            'role_id' => 4,
            'email' => "b.shakhrukh@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "М.Н.МирАхмадова",
            'sector_id' => 2,
            'role_id' => 4,
            'email' => "m.mirakhmadova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Х.З.Мажидов",
            'sector_id' => 3,
            'role_id' => 3,
            'email' => "x.majidov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "О.О.Одилов",
            'sector_id' => 3,
            'role_id' => 3,
            'email' => "o.odilov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "К.Нуриллаев",
            'sector_id' => 3,
            'role_id' => 4,
            'email' => "k.nurillaev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Р.В.Абатуров",
            'sector_id' => 4,
            'role_id' => 3,
            'email' => "r.abaturov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "И.Худайберганов",
            'sector_id' => 4,
            'role_id' => 3,
            'email' => "i.khudayberganov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Мирошникова Д.",
            'sector_id' => 4,
            'role_id' => 4,
            'email' => "d.miroshnikova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Р.Р.Абдуллаев",
            'sector_id' => 5,
            'role_id' => 3,
            'email' => "r.abdullaev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.Х.Камалов",
            'sector_id' => 6,
            'role_id' => 3,
            'email' => "a.kamalov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "М.Б.Жуманиёзова",
            'sector_id' => 6,
            'role_id' => 4,
            'email' => "m.jumaniyozova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.Т.Бозоров",
            'sector_id' => 7,
            'role_id' => 3,
            'email' => "a.bozorov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Н.Б.Курбанбаева",
            'sector_id' => 7,
            'role_id' => 3,
            'email' => "n.kurbanbaeva@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Б.А.Исмоилов",
            'sector_id' => 7,
            'role_id' => 4,
            'email' => "b.ismailov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "З.Анваров",
            'sector_id' => 8,
            'role_id' => 3,
            'email' => "z.anvarov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Б.Туракулов",
            'sector_id' => 8,
            'role_id' => 3,
            'email' => "b.to'raqulov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Г.Азимова",
            'sector_id' => 8,
            'role_id' => 3,
            'email' => "g.azimova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "У.Мамасалаев",
            'sector_id' => 8,
            'role_id' => 3,
            'email' => "u.mamasalaeva@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Л.А.Буриева",
            'sector_id' => 8,
            'role_id' => 4,
            'email' => "l.buriyeva@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ю.Утепбергенов",
            'sector_id' => 9,
            'role_id' => 4,
            'email' => "y.utepbergenov@cerr.uz",
            'password' => Hash::make('yu3667500')
        ],
        [
            'name' => "И.Раббимов",
            'sector_id' => 9,
            'role_id' => 4,
            'email' => "i.rabbimov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ф.Туткабаев",
            'sector_id' => 9,
            'role_id' => 4,
            'email' => "f.tutkabaev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "М.К.Бабаданов",
            'sector_id' => 10,
            'role_id' => 3,
            'email' => "m.babadjanov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.Халмурзаев",
            'sector_id' => 10,
            'role_id' => 3,
            'email' => "a.halmurzaev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Т.Ниязматов",
            'sector_id' => 10,
            'role_id' => 3,
            'email' => "t.niyazmatov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "В.Абатуров",
            'sector_id' => 11,
            'role_id' => 2,
            'email' => "v.abaturov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "В.Оганьян",
            'sector_id' => 11,
            'role_id' => 3,
            'email' => "v.oganyan@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "C.Абатуров",
            'sector_id' => 11,
            'role_id' => 3,
            'email' => "s.abaturov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "В.Луконин",
            'sector_id' => 11,
            'role_id' => 4,
            'email' => "v.lukonin@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "С.Бегманов",
            'sector_id' => 12,
            'role_id' => 3,
            'email' => "s.begmanov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ю.Жумабаев",
            'sector_id' => 12,
            'role_id' => 4,
            'email' => "y.jumabaev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ф.Рахимов",
            'sector_id' => 13,
            'role_id' => 3,
            'email' => "f.raximov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "У.Хужакулов",
            'sector_id' => 13,
            'role_id' => 3,
            'email' => "u.khujakulov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Э.Пулатов",
            'sector_id' => 13,
            'role_id' => 4,
            'email' => "e.pulatov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ш.Нурдинова",
            'sector_id' => 14,
            'role_id' => 3,
            'email' => "sh.nurdinova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Б.Хусанов",
            'sector_id' => 15,
            'role_id' => 3,
            'email' => "b.khusanov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.Кобилов",
            'sector_id' => 16,
            'role_id' => 2,
            'email' => "a.kobilov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Э.Зайниддинов",
            'sector_id' => 16,
            'role_id' => 3,
            'email' => "e.zayniddinov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ф.Исломов",
            'sector_id' => 16,
            'role_id' => 4,
            'email' => "f.islomov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Н.Н.Ли",
            'sector_id' => 1,
            'role_id' => 5,
            'email' => "n.li@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.А.Абдукаххоров",
            'sector_id' => 1,
            'role_id' => 6,
            'email' => "a.abduqaxxorov@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Ф.А.Абдурахмонова",
            'sector_id' => 1,
            'role_id' => 7,
            'email' => "f.abduraxmonova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "А.А.Манунцев",
            'sector_id' => 1,
            'role_id' => 8,
            'email' => "a.manuncev@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Д.Закирова",
            'sector_id' => 1,
            'role_id' => 9,
            'email' => "d.zakirova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Н.Очилова",
            'sector_id' => 1,
            'role_id' => 10,
            'email' => "n.ochilova@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Р.Мардонов",
            'sector_id' => 1,
            'role_id' => 11,
            'email' => "neoradmin@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Г.Матрузиева",
            'sector_id' => 1,
            'role_id' => 12,
            'email' => "g.matruzayeva@cerr.uz",
            'password' => Hash::make('password')
        ],
        [
            'name' => "Шамсиддинов Ш.",
            'sector_id' => 1,
            'role_id' => 13,
            'email' => "sh.shamsiddinov@cerr.uz",
            'password' => Hash::make('password')
        ],
    ]);
    }
}
