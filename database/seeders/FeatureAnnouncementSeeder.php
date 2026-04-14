<?php

namespace Database\Seeders;

use App\Models\FeatureAnnouncement;
use Illuminate\Database\Seeder;

class FeatureAnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        FeatureAnnouncement::firstOrCreate(
            ['title_ru' => 'Новая функция: Объявления о возможностях'],
            [
                'title_uz' => 'Yangi funksiya: Yangiliklar paneli',
                'body_ru' => "Теперь вы будете узнавать о новых возможностях системы прямо внутри приложения.\n\n- Панель откроется автоматически при появлении новых объявлений\n- Нажмите **Понятно**, чтобы закрыть её\n- Админ может добавлять новые объявления в разделе управления",
                'body_uz' => "Endi tizimdagi yangi imkoniyatlar haqida ilovaning o'zida bilib olasiz.\n\n- Yangi e'lon paydo bo'lganda panel avtomatik ochiladi\n- Yopish uchun **Tushundim** tugmasini bosing",
                'published_at' => now(),
                'target_all' => true,
            ]
        );
    }
}
