<?php

namespace Database\Factories;

use App\Models\MailDocument;
use App\Models\MailItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailItem>
 */
class MailItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mail_document_id' => MailDocument::factory(),
            'clause' => fake()->numberBetween(1, 9).'-банд',
            'content' => fake()->sentence(12),
            'position' => 0,
        ];
    }
}
