<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'creator_id' => $this->faker->randomDigitNotNull(),
            'user_id' => $this->faker->numberBetween(1, 45),
            'sector_id' => $this->faker->numberBetween(1, 8),
            'project_id' => $this->faker->numberBetween(1, 5),
            'name' => $this->faker->sentence(8),
            'description' => $this->faker->sentence(20),
            'deadline' => $this->faker->date('Y-m-d'),
            'status' => "Новое"
        ];
    }
}
