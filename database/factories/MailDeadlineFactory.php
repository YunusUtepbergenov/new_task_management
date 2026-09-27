<?php

namespace Database\Factories;

use App\Models\MailDeadline;
use App\Models\MailItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailDeadline>
 */
class MailDeadlineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mail_item_id' => MailItem::factory(),
            'deadline' => fake()->dateTimeBetween('+1 day', '+2 months')->format('Y-m-d'),
            'status' => MailDeadline::STATUS_PENDING,
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => ['deadline' => today()->subDays(10)->toDateString()]);
    }

    public function done(): static
    {
        return $this->state(fn () => ['status' => MailDeadline::STATUS_DONE, 'completed_at' => now()]);
    }
}
