<?php

namespace Database\Factories;

use App\Models\MailDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailDocument>
 */
class MailDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['ЎзР Президенти ҳужжатлари', 'ЎзР Вазирлар Маҳкамаси ҳужжатлари']),
            'document_number' => 'ПҚ-'.fake()->numberBetween(1, 500),
            'document_date' => fake()->dateTimeBetween('-3 months', '-2 months')->format('Y-m-d'),
            'title' => fake()->sentence(10),
        ];
    }
}
