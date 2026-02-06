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
        // Simulate year/month
        $year = $this->faker->numberBetween(2024, 2026);
        $month = $this->faker->numberBetween(1, 12);

        // Very simplified static counter for factory demo consistency
        // Note: in parallel tests this might be flaky, but for seeding it works
        static $counters = [];
        if (!isset($counters[$year])) {
            $counters[$year] = 0;
        }
        $counters[$year]++;

        $code = sprintf('%04d-%02d-%04d', $year, $month, $counters[$year]);

        return [
            'code' => $code,
            'title' => $this->faker->sentence(4),
            'year' => $year,
            'month' => $month,
            'user_id' => \App\Models\User::factory(),
            'note' => $this->faker->paragraph(),
            'notes' => $this->faker->paragraph(),
            'client' => $this->faker->company(),
            'client_id' => (string) $this->faker->numberBetween(100, 999),
            'supplier' => $this->faker->company(),
            'order_number' => $this->faker->bothify('ORD-####'),
            'supplier_order_number' => $this->faker->bothify('SUP-####'),
            'ddt_number' => $this->faker->bothify('DDT-####'),
            'invoice_number' => $this->faker->bothify('INV-####'),
            'invoice_year' => $year,
            'status' => $this->faker->randomElement(['active', 'archived']),
        ];
    }
}
