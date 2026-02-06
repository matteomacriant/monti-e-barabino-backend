<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Practice>
 */
class PracticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('PR-#####'),
            'title' => $this->faker->sentence(4),
            'year' => $this->faker->numberBetween(2020, 2026),
            'month' => $this->faker->numberBetween(1, 12),
            'user_id' => \App\Models\User::factory(),
            'note' => $this->faker->paragraph(),
            'notes' => $this->faker->paragraph(), // Populating both to be safe due to schema ambiguity
            'client' => $this->faker->company(),
            'client_id' => (string) $this->faker->numberBetween(100, 999),
            'supplier' => $this->faker->company(),
            'order_number' => $this->faker->bothify('ORD-####'),
            'supplier_order_number' => $this->faker->bothify('SUP-####'),
            'ddt_number' => $this->faker->bothify('DDT-####'),
            'invoice_number' => $this->faker->bothify('INV-####'),
            'invoice_year' => $this->faker->year(),
            'status' => $this->faker->randomElement(['active', 'archived', 'pending']),
        ];
    }
}
